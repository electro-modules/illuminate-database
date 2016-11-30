<?php
namespace Electro\Plugins\IlluminateDatabase;

class Schema
{
  /** @var DatabaseAPI */
  private static $api;

  public static function __callStatic ($method, $args)
  {
    return self::$api->schema ()->$method (...$args);
  }

  public static function setInstance (DatabaseAPI $api)
  {
    self::$api = $api;
  }

}
