<?php
namespace Selenia\Plugins\IlluminateDatabase;

class Schema
{
  public static function __callStatic ($method, $args)
  {
    return Database::schema ()->$method (...$args);
  }

}
