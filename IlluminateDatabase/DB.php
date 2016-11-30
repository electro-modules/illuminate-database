<?php
namespace Electro\Plugins\IlluminateDatabase;

/**
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

  public static function setInstance (DatabaseAPI $api)
  {
    self::$api = $api;
  }

}
