<?php
namespace Electro\Plugins\IlluminateDatabase\Config;

use Illuminate\Events\Dispatcher;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\DI\ServiceProviderInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Electro\Plugins\IlluminateDatabase\Services\ModelController;

class IlluminateDatabaseModule implements ServiceProviderInterface
{
  function register (InjectorInterface $injector)
  {
    $injector
      ->share (DatabaseAPI::class)
      ->prepare (DatabaseAPI::class, function (DatabaseAPI $db) {
        $db->manager->setAsGlobal ();
        $db->manager->setEventDispatcher (new Dispatcher($db->manager->getContainer ()));
        $db->manager->bootEloquent ();
      })
      ->alias (ModelControllerInterface::class, ModelController::class)
      ->share (ModelController::class);
  }

}
