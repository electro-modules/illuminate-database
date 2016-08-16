<?php
namespace Electro\Plugins\IlluminateDatabase\Services;

use Electro\Interfaces\MigrationsInterface;

class Migrations implements MigrationsInterface
{
  function migrate ($moduleName, $target = null)
  {
    // TODO: Implement migrate() method.
  }

  function reset ($moduleName)
  {
    // TODO: Implement reset() method.
  }

  function rollback ($moduleName, $target = null, $date = null)
  {
    // TODO: Implement rollback() method.
  }

  function seed ($moduleName, $seeder = null)
  {
    // TODO: Implement seed() method.
  }

  function status ($moduleName, $onlyPending = false)
  {
    // TODO: Implement status() method.
  }

}
