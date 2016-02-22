<?php
namespace Selenia\Plugins\IlluminateDatabase\Config;

use Selenia\Interfaces\InjectorInterface;
use Selenia\Interfaces\ServiceProviderInterface;
use Selenia\Plugins\IlluminateDatabase\DatabaseAPI;

class IlluminateDatabaseModule implements ServiceProviderInterface
{
  function register (InjectorInterface $injector)
  {
    $injector
      ->share (DatabaseAPI::class)
      ->prepare (DatabaseAPI::class, function (DatabaseAPI $db) {
        $db->manager->setAsGlobal ();
        $db->manager->bootEloquent ();
      });
  }

}
