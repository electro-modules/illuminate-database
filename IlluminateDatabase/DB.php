<?php

namespace Electro\Plugins\IlluminateDatabase;

use Electro\Plugins\IlluminateDatabase\Config\IlluminateDatabaseModule;

/**
 * Using this class is discouraged, except on database migrations. You should always inject your dependencies instead
 * of using global singletons.
 *
 * <p>The purpose of this Laravel-like 'facade' is to ease the conversion of existing Laravel migrations to Electro
 * migrations.
 * <p>Use the {@see DatabaseAPI} injectable class instead.
 *
 * @method static \Illuminate\Database\Query\Builder table(string $table) Begin a fluent query against a database
 *         table.
 * @method static mixed transaction(callable $fn)
 */
class DB
{
  /**
   * Lazily initializes the Illuminate database adapter when accessing the Query Builder statically.
   *
   * @param  string $method
   * @param  array  $args
   * @return mixed
   * @throws \Auryn\InjectionException
   */
  public static function __callStatic ($method, $args)
  {
    return IlluminateDatabaseModule::getAPI ()->connection ()->$method (...$args);
  }

  /**
   * Retrieves the connection woth the given name.
   *
   * @param string $name The connection name.
   * @return \Illuminate\Database\Connection
   * @throws \Auryn\InjectionException
   */
  public static function connection ($name)
  {
    return IlluminateDatabaseModule::getAPI ()->connection ($name);
  }

}
