<?php
namespace Electro\Plugins\IlluminateDatabase\Config;

use Electro\ConsoleApplication\Config\ConsoleSettings;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\Migrations\MigrationsInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Kernel\Lib\ModuleInfo;
use Electro\Kernel\Services\Bootstrapper;
use Electro\Plugins\IlluminateDatabase\Commands\MigrationCommands;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Electro\Plugins\IlluminateDatabase\Services\Migrations;
use Electro\Plugins\IlluminateDatabase\Services\ModelController;
use Illuminate\Events\Dispatcher;
use const Electro\Kernel\Services\CONFIGURE;
use const Electro\Kernel\Services\REGISTER_SERVICES;

class IlluminateDatabaseModule implements ModuleInterface
{
  static function bootUp (Bootstrapper $bootstrapper, ModuleInfo $moduleInfo)
  {
    $bootstrapper
      //
      ->on (REGISTER_SERVICES, function (InjectorInterface $injector) {
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
      })
      //
      ->on (CONFIGURE, function (ConsoleSettings $consoleSettings) {
        $consoleSettings->registerTasksFromClass (MigrationCommands::class);
      });
  }

}
