<?php
namespace Electro\Plugins\IlluminateDatabase\Services;

use Electro\Core\Assembly\ModuleInfo;
use Electro\Core\Assembly\Services\ModulesRegistry;
use Electro\Interfaces\MigrationsInterface;
use Electro\Interop\MigrationStruct as Migration;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Electro\Plugins\IlluminateDatabase\Migrations\Config\MigrationsSettings;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use PhpKit\Flow\FilesystemFlow;

class Migrations implements MigrationsInterface
{
  const MIGRATIONS_TABLE = 'migrations';
  /**
   * @var DatabaseAPI
   */
  private $databaseAPI;
  /**
   * @var string
   */
  private $migrationsPath;
  /**
   * @var ModuleInfo
   */
  private $module;
  /**
   * @var ModulesRegistry
   */
  private $registry;
  /**
   * @var string
   */
  private $seedsPath;
  /**
   * @var MigrationsSettings
   */
  private $settings;


  public function __construct (DatabaseAPI $databaseAPI, MigrationsSettings $settings, ModulesRegistry $registry)
  {
    $this->databaseAPI = $databaseAPI;
    $this->settings    = $settings;
    $this->registry    = $registry;
  }

  function migrate ($target = null)
  {
    $this->assertModuleIsSet ();

  }

  function module ($moduleName)
  {
    $this->module         = $this->registry->getModule ($moduleName);
    $this->migrationsPath = "{$this->module->path}/{$this->settings->migrationsPath ()}";
    $this->seedsPath      = "{$this->module->path}/{$this->settings->seedsPath ()}";
    $this->createMigrationsTableIfRequired ();
    return $this;
  }

  function reset ()
  {
    $this->assertModuleIsSet ();

  }

  function rollback ($target = null, $date = null)
  {
    $this->assertModuleIsSet ();

  }

  function seed ($seeder = null)
  {
    $this->assertModuleIsSet ();

  }

  function status ($onlyPending = false)
  {
    $this->assertModuleIsSet ();
    $all = $this->getTable ()->orderBy (Migration::date)->get ();
    return $onlyPending ? array_findAll ($all, Migration::status, Migration::DOWN) : $all;
  }

  private function assertModuleIsSet ()
  {
    if (!$this->module)
      throw new \RuntimeException("Target module is not set");
  }

  private function createMigrationsTableIfRequired ()
  {
    $schema = $this->databaseAPI->schema ();
    if (!$schema->hasTable (self::MIGRATIONS_TABLE))
      $schema->create (self::MIGRATIONS_TABLE, function (Blueprint $table) {
        $table->increments (Migration::id);
        $table->string (Migration::date, 14);
        $table->string (Migration::module, 64);
        $table->string (Migration::name, 128);
        $table->string (Migration::status, 4);
        $table->string (Migration::reverse, 1024);
        $table->index (Migration::date);
        $table->index (Migration::module);
      });
  }

  /**
   * @return Builder
   */
  private function getTable ()
  {
    return $this->databaseAPI->table (self::MIGRATIONS_TABLE)->where (Migration::module, $this->module->name);
  }

  /**
   * @return string[]
   */
  private function getAllFiles ()
  {
    return FilesystemFlow::glob("{$this->migrationsPath}/*.php")->keys()->sort()->all ();
  }

}
