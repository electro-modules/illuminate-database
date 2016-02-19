<?php
namespace Selenia\Plugins\IlluminateDatabase\Config;

use Selenia\Interfaces\InjectorInterface;
use Selenia\Interfaces\ServiceProviderInterface;
use Selenia\Plugins\IlluminateDatabase\Database;

class IlluminateDatabaseModule implements ServiceProviderInterface
{
  function register (InjectorInterface $injector)
  {
    $injector
      ->share (Database::class)
      ->prepare (Database::class, function (Database $db) {
        $db->setAsGlobal ();
        $db->bootEloquent ();
      });
  }

}
