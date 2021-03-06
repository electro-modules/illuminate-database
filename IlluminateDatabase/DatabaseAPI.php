<?php

namespace Electro\Plugins\IlluminateDatabase;

use Electro\Debugging\Config\DebugSettings;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolverInterface;
use PhpKit\ExtPDO\Interfaces\ConnectionsInterface;

/**
 * A mediator for the Illuminate Database API.
 *
 * <p>It integrates with PhpKit\ExtPDO and the framework's Database subsystem, so that connections defined on the
 * framework's {@see Connections} service are automatically available as Illuminate Database connections.
 *
 * > <b>WARNING! <br>
 * > You cannot subclass this class!</b><br>
 * > It would cause serious problems, such as duplicated connections being created and never closed.<br>
 * > On long running scripts, this will eventually accumulate too much opened connections and reach the limit imposed
 * > by the database server.
 */
final class DatabaseAPI implements ConnectionResolverInterface
{
  /**
   * @var bool Enforces the singleton pattern.
   */
  private static $created = false;
  /**
   * The database manager instance.
   *
   * @var Manager
   */
  public $manager;
  /**
   * @var ConnectionsInterface
   */
  private $connections;
  /**
   * @var string The default connection's name.
   */
  private $defaultConnection = 'default';

  public function __construct (ConnectionsInterface $connections, DebugSettings $debugSettings)
  {
    if (self::$created)
      throw new \LogicException(self::class .
                                ' is a singleton service; you may inject it but you cannot instantiante it manually.');
    self::$created     = true;
    $this->manager     = new Manager;
    $this->connections = $connections;
    $con               = $this->manager->getContainer ();
    $logPdo            = $debugSettings->isWebConsoleAvailable () && $debugSettings->logDatabase;
    $factory           = function () use ($logPdo) { return new ElectroConnector ($logPdo); };

    $con->bind ("db.connector.mysql", $factory, true);
    $con->bind ("db.connector.pgsql", $factory, true);
    $con->bind ("db.connector.sqlite", $factory, true);
    $con->bind ("db.connector.sqlsrv", $factory, true);
  }

  /**
   * Returns an instance of the Illuminate Database connection having the specified name, or the default connection if
   * no name is given.
   *
   * @param string $connectionName [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Connection
   */
  public function connection ($connectionName = null)
  {
    $connectionName = $connectionName ?: $this->defaultConnection;
    try {
      return $this->manager->getConnection ($connectionName);
    }
    catch (\InvalidArgumentException $e) {
      $connectionName = $connectionName ?: 'default';
      $con            = $this->connections->get ($connectionName);
      $props          = $con->getProperties ();
      // FIX: Illuminate requires 'host' to be non empty, even for SQLite.
      $props['host'] = get ($props, 'host', 'localhost');
      $this->manager->addConnection ($props, $connectionName);
      return $this->manager->getConnection ($connectionName);
    }
  }

  /**
   * Get the default connection name.
   *
   * @return string
   */
  public function getDefaultConnection ()
  {
    return $this->defaultConnection;
  }

  /**
   * Set the default connection name.
   *
   * @param string $name
   * @return void
   */
  public function setDefaultConnection ($name)
  {
    $this->defaultConnection = $name;
  }

  /**
   * Gets the database connection registry.
   *
   * @return ConnectionsInterface
   */
  public function getConnections ()
  {
    return $this->connections;
  }

  /**
   * Checks if a table exists, even if the connection is on "pretending" mode.
   *
   * ><p>**Note:** this method also prevents its database queries from being logged by the
   * {@see \Illuminate\Database\Connection} object.<br>
   * This is required for making sure they do not become part of the generated migration/rollback SQL code.
   *
   * @param string $table          The table name.
   * @param string $connectionName [optional] A connection name, if you want to use a connection other than the
   *                               default.
   * @return bool true if the table exists.
   */
  public function hasTable ($table, $connectionName = null)
  {
    $schema     = $this->schema ($connectionName);
    $con        = $this->connection ();
    $pretending = $con->pretending ();
    if (!$pretending)
      return $schema->hasTable ($table);
    forceSetProperty ($con, 'pretending', false);
    $logging = $con->logging ();
    $con->disableQueryLog ();
    $v = $schema->hasTable ($table);
    forceSetProperty ($con, 'pretending', true);
    if ($logging)
      $con->enableQueryLog ();
    return $v;
  }

  /**
   * Returns an instance of the Illuminate Database query builder.
   *
   * @param string $connectionName [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Query\Builder
   */
  public function query ($connectionName = null)
  {
    return $this->connection ($connectionName)->query ();
  }

  /**
   * Returns an instance of the Illuminate Database schema builder.
   *
   * @param string $connectionName [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Schema\Builder
   */
  public function schema ($connectionName = null)
  {
    return $this->connection ($connectionName)->getSchemaBuilder ();
  }

  /**
   * Returns an instance of the Illuminate Database query builder, bound to a specific table.
   *
   * @param string $table          Table name,
   * @param string $connectionName [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Query\Builder
   */
  public function table ($table, $connectionName = null)
  {
    return $this->query ($connectionName)->from ($table);
  }

}
