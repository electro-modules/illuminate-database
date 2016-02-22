<?php
namespace Selenia\Plugins\IlluminateDatabase;

use Illuminate\Database\Capsule\Manager;

class DB
{
  public static function __callStatic ($method, $args)
  {
    return Manager::connection ()->query ()->$method (...$args);
  }

}
