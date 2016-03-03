<?php
namespace Selenia\Plugins\IlluminateDatabase;

use Illuminate\Database\Capsule\Manager;
use PhpKit\ConnectionInterface;

/**
 * An interface to the Illuminate Database API.
 *
 * ### Facades
 *
 * This plugin also emulates some common database-related Laravel facades:
 *
 * - `DB::method()`     - equivalent to `$this->query()->method()`
 * - `Schema::method()` - equivalent to `$this->schema()->method()`
 *
 * > **Note:** being an anti-pattern, facades are not recommended for development with Selenia.
 * <p><br>
 * > **Note:** be sure to import the related namespaces before using the facades:
 * > - `use Selenia\Plugins\IlluminateDatabase\DB;`
 * > - `use Selenia\Plugins\IlluminateDatabase\Schema;`
 *
 * > **Note:** facades require a global shared context, so remember to inject an instance of this class before using any
 * of them. You don't need to use the injected instance, but just by injecting it, you'll setup the required global
 * context.
 *
 * ### Eloquent
 *
 * To use Eloquent, access your models as usual, but remember to inject an instance of this class before using any
 * model.
 *
 * ###### Ex:
 *
 * ```
 * use Selenia\Plugins\IlluminateDatabase\DatabaseAPI;
 *
 * class MyClass {
 *    // you must inject an instance of DatabaseAPI before using Eloquent.
 *    function __construct (DatabaseAPI $db) {
 *      // You don't have to do anything with $db, though.
 *    }
 *    function getTheUser () {
 *      return User::find(1);
 *    }
 * }
 * ```
 */
class DatabaseAPI
{
  /**
   * The database manager instance.
   *
   * @var Manager
   */
  public $manager;

  public function __construct (ConnectionInterface $connection)
  {
    $this->manager = new Manager;
    $this->manager->addConnection ($connection->getProperties ());
  }

  /**
   * Returns an instance of the Illuminate Database connection having the speified name, or the default connection if no
   * name is given.
   *
   * @param string $connectionName [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Connection
   */
  public function connection ($connectionName = null)
  {
    return $this->manager->getConnection ($connectionName);
  }

  /**
   * Returns an instance of the Illuminate Database query builder.
   *
   * @param string $connectionName [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Query\Builder
   */
  public function query ($connectionName = null)
  {
    return $this->manager->getConnection ($connectionName)->query ();
  }

  /**
   * Returns an instance of the Illuminate Database schema builder.
   *
   * @param string $connectionName [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Schema\Builder
   */
  public function schema ($connectionName = null)
  {
    return $this->manager->getConnection ($connectionName)->getSchemaBuilder ();
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

  public function updateMultipleSelection ($table, $field, array $selectedIDs, $pk = 'id', $connectionName = null)
  {
    $this->connection ($connectionName)->transaction (function () use ($table, $field, $selectedIDs, $pk) {
      $table = $this->table ($table);
      $table->update ([$field => 0]);
      $table->whereIn ($pk, $selectedIDs)->update ([$field => 1]);
    });
  }

}
