<?php
namespace Selenia\Plugins\IlluminateDatabase;

class DB
{
  public static function __callStatic ($method, $args)
  {
    return Database::query ()->$method (...$args);
  }

}
