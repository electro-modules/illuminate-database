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
 */
class Schema
{
  public static function __callStatic ($method, $args)
  {
    return IlluminateDatabaseModule::getAPI ()->connection ()->$method (...$args);
  }

}
