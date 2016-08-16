<?php
namespace Electro\Plugins\IlluminateDatabase\Config;

use Electro\Core\Assembly\Services\ModuleServices;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\DI\ServiceProviderInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Plugins\IlluminateDatabase\Migrations\Commands\MigrationCommands;
use Electro\Plugins\IlluminateDatabase\Migrations\Config\MigrationsSettings;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Electro\Plugins\IlluminateDatabase\Services\ModelController;
use Illuminate\Events\Dispatcher;

class IlluminateDatabaseModule implements ServiceProviderInterface, ModuleInterface
{
  function configure (ModuleServices $module)
  {
    $module->registerTasksFromClass (MigrationCommands::class);
  }

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
      ->share (ModelController::class)
      ->share (MigrationsSettings::class);
  }

}
