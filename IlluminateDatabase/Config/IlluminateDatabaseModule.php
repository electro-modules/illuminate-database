<?php
namespace Electro\Plugins\IlluminateDatabase\Config;

use Electro\Core\Assembly\Services\Bootstrapper;
use Electro\Core\Assembly\Services\ModuleServices;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\Migrations\MigrationsInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Plugins\IlluminateDatabase\Commands\MigrationCommands;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Electro\Plugins\IlluminateDatabase\Services\Migrations;
use Electro\Plugins\IlluminateDatabase\Services\ModelController;
use Illuminate\Events\Dispatcher;

class IlluminateDatabaseModule implements ModuleInterface
{
  static function boot (Bootstrapper $boot)
  {
    $boot->on (Bootstrapper::EVENT_BOOT, function (InjectorInterface $injector, ModuleServices $module) {
      $injector
        ->share (DatabaseAPI::class)
        ->prepare (DatabaseAPI::class, function (DatabaseAPI $db) {
          $db->manager->setAsGlobal ();
          $db->manager->setEventDispatcher (new Dispatcher($db->manager->getContainer ()));
          $db->manager->bootEloquent ();
        })
        ->alias (ModelControllerInterface::class, ModelController::class)
        ->share (ModelController::class)
        ->share (MigrationsSettings::class)
        ->alias (MigrationsInterface::class, Migrations::class);

      $module->registerTasksFromClass (MigrationCommands::class);
    });
  }

}
