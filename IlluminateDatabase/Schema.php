<?php
namespace Electro\Plugins\IlluminateDatabase;

use Illuminate\Database\Capsule\Manager;

class Schema
{
  public static function __callStatic ($method, $args)
  {
    return Manager::schema ()->$method (...$args);
  }

}
