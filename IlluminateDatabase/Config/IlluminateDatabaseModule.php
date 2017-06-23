<?php
namespace Electro\Plugins\IlluminateDatabase\Config;

use Electro\ConsoleApplication\Config\ConsoleSettings;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\KernelInterface;
use Electro\Interfaces\Migrations\MigrationsInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Kernel\Lib\ModuleInfo;
use Electro\Plugins\IlluminateDatabase\Commands\MigrationCommands;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Electro\Plugins\IlluminateDatabase\DB;
use Electro\Plugins\IlluminateDatabase\Schema;
use Electro\Plugins\IlluminateDatabase\Services\ExceptionHandler;
use Electro\Plugins\IlluminateDatabase\Services\Migrations;
use Electro\Plugins\IlluminateDatabase\Services\ModelController;
use Electro\Profiles\ConsoleProfile;
use Electro\Profiles\WebProfile;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;

class IlluminateDatabaseModule implements ModuleInterface
{
  /**
   * This is required by {@see deferredInit()}
   *
   * @var InjectorInterface
   */
  private static $injector;

  /**
   * This is called to lazy-instantiate required services when accessing Eloquent models statically.
   *
   * @return DatabaseAPI
   * @throws \Auryn\InjectionException
   */
  static function getAPI ()
  {
    return self::$injector->make (DatabaseAPI::class);
  }

  static function getCompatibleProfiles ()
  {
    return [WebProfile::class, ConsoleProfile::class];
  }

  static function startUp (KernelInterface $kernel, ModuleInfo $moduleInfo)
  {
    $kernel
      ->onRegisterServices (
        function (InjectorInterface $injector) {
          self::$injector = $injector;
          $injector
            ->share (DatabaseAPI::class)
            ->prepare (DatabaseAPI::class, function (DatabaseAPI $db) {
              $db->manager->setAsGlobal ();
              $db->manager->setEventDispatcher (new Dispatcher($db->manager->getContainer ()));
              $db->manager->bootEloquent ();
              Model::setConnectionResolver ($db);
              $db->manager->getContainer ()->singleton (ExceptionHandlerInterface::class, ExceptionHandler::class);
            })
            ->alias (ModelControllerInterface::class, ModelController::class)
            ->share (ModelController::class)
            ->share (MigrationsSettings::class)
            ->alias (MigrationsInterface::class, Migrations::class);
        });

    if ($kernel->getProfile () instanceof ConsoleProfile)
      $kernel
        ->onConfigure (
          function (ConsoleSettings $consoleSettings) {
            $consoleSettings->registerTasksFromClass (MigrationCommands::class);
          });
  }

}
