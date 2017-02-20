<?php

namespace Electro\Plugins\IlluminateDatabase;

/**
 * Please DO NOT USE this class, except on database migrations.
 *
 * <p>Its purpose is to ease the conversion of existing Laravel migrations to Electro migrations.
 * <p>Use the {@see DatabaseAPI} injectable class instead.
 *
 * @method static \Illuminate\Database\Query\Builder table(string $table) Begin a fluent query against a database table.
 */
class DB
{
  /** @var DatabaseAPI */
  private static $api;

  public static function __callStatic ($method, $args)
  {
    return self::$api->connection ()->$method (...$args);
  }

  /**
   * @param string $name The connection name.
   * @return \Illuminate\Database\Connection
   */
  public static function connection ($name)
  {
    return self::$api->connection ($name);
  }

  public static function setInstance (DatabaseAPI $api)
  {
    self::$api = $api;
  }

}
