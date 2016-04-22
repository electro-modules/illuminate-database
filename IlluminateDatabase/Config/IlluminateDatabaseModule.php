<?php
namespace Selenia\Plugins\IlluminateDatabase\Config;

use Illuminate\Events\Dispatcher;
use Selenia\Interfaces\DI\InjectorInterface;
use Selenia\Interfaces\DI\ServiceProviderInterface;
use Selenia\Interfaces\ModelControllerInterface;
use Selenia\Plugins\IlluminateDatabase\DatabaseAPI;
use Selenia\Plugins\IlluminateDatabase\Services\ModelController;

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
