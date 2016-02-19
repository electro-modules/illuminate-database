<?php
namespace Selenia\Plugins\IlluminateDatabase;

use Illuminate\Database\Capsule\Manager;
use PhpKit\ConnectionInterface;

/**
 * An interface to the Illuminate Database API.
 *
 * ### The instance-level API
 *
 * An instance of this class provides several methods, from which these are the most relevant:
 *
 * - `getConnection()`  - returns an instance of an Illuminate Database connection.
 * - `getSchema()`      - returns an instance of the Illuminate Database schema builder.
 * - `getQuery()`       - returns an instance of the Illuminate Database query builder.
 * - `fromTable($name)` - a shortcut to `$this->getQuery()->table($name)`.
 *
 * ### The static API
 *
 * There are also static variants of the above methods. They are:
 *
 * - `Database::connection()`
 * - `Database::schema()`
 * - `Database::query()`
 * - `Database::table()`
 *
 * > **Note:** being an anti-pattern, singletons and global shared state are not recommended for development with
 * Selenia.
 *
 * #### Setting up the static API
 *
 * Before using any static method, you must set a global shared database context.
 *
 * > This also applies to facades and to Eloquent (see below).
 *
 * <p>Do one of the following:
 * - inject an instance of this class into your class constructor, or
 * - directly instantiate this class somewhere and call `setAsGlobal()` on it.
 *
 * > **Note:** Selenia sets up the global context automatically for you, but you must inject this class at least once
 * somewhere, otherwise Selenia never creates it because it assumes you don't need it on the current HTTP request or
 * console command.
 *
 * ### Facades
 *
 * This plugin also emulates some common database-related Laravel facades:
 *
 * - `DB::method()`     - equivalent to `Database::query()->method()`
 * - `Schema::method()` - equivalent to `Database::schema()->method()`
 *
 * > **Note:** being an anti-pattern, facades are not recommended for development with Selenia.
 * <p><br>
 * > **Note:** be sure to import the related namespaces before using the facades:
 * > - `use Selenia\Plugins\IlluminateDatabase\DB;`
 * > - `use Selenia\Plugins\IlluminateDatabase\Schema;`
 *
 * > **Note:** facades require a global shared context. See the explanation further above on how to set it up.
 *
 * ### Eloquent
 *
 * To use Eloquent, access your models as usual, but remember to inject an instance of this class before using any
 * model.
 *
 * ###### Ex:
 *
 * ```
 * use Selenia\Plugins\IlluminateDatabase\Database;
 *
 * class MyClass {
 *    // you must inject an instance of Database before using Eloquent.
 *    function __construct (Database $db) {
 *      // You don't have to do anything with $db, though.
 *    }
 *    function getTheUser () {
 *      return User::find(1);
 *    }
 * }
 * ```
 *
 * > **Note:** unfortunately, global shared state cannot be avoided when using Eloquent.
 */
class Database extends Manager
{
  public function __construct (ConnectionInterface $connection)
  {
    parent::__construct ();
    $this->addConnection ($connection->getProperties ());
  }

  /**
   * Returns an instance of the Illuminate Database query builder.
   *
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Query\Builder
   */
  static public function query ($connection = null)
  {
    return static::connection ($connection)->query ();
  }

  /**
   * Returns an instance of the Illuminate Database query builder.
   *
   * @param string $table      Table name,
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Query\Builder
   */
  public function fromTable ($table, $connection = null)
  {
    return $this->getQuery ($connection)->from ($table);
  }

  /**
   * Returns an instance of the Illuminate Database query builder.
   *
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Query\Builder
   */
  public function getQuery ($connection = null)
  {
    return $this->getConnection ($connection)->query ();
  }

  /**
   * Returns an instance of the Illuminate Database schema builder.
   *
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Schema\Builder
   */
  public function getSchema ($connection = null)
  {
    return $this->getConnection ($connection)->getSchemaBuilder ();
  }

}
