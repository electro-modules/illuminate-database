<?php
namespace Electro\Plugins\IlluminateDatabase\Services;

use Electro\Core\Assembly\ModuleInfo;
use Electro\Core\Assembly\Services\ModulesRegistry;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\Migrations\MigrationInterface;
use Electro\Interfaces\Migrations\MigrationsInterface;
use Electro\Interop\MigrationStruct as Migration;
use Electro\Plugins\IlluminateDatabase\Config\MigrationsSettings;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use PhpKit\Flow\FilesystemFlow;

class Migrations implements MigrationsInterface
{
  const MIGRATIONS_TABLE = 'migrations';
  /**
   * A special SQL-compatible marquer that allows the migrator to split a saved query block into multiple individual
   * queries while preventing false positives.
   */
  const QUERY_DELIMITER = ";--\n";
  /**
   * @var DatabaseAPI
   */
  private $databaseAPI;
  /**
   * @var InjectorInterface
   */
  private $injector;
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


  public function __construct (DatabaseAPI $databaseAPI, MigrationsSettings $settings, ModulesRegistry $registry,
                               InjectorInterface $injector)
  {
    $this->databaseAPI = $databaseAPI;
    $this->settings    = $settings;
    $this->registry    = $registry;
    $this->injector    = $injector;
  }

  function migrate ($target = null, $pretend = false)
  {
    $this->assertModuleIsSet ();
    $all      = $this->getAllMigrations ();
    $pending  = PA ($all)->findAll (Migration::status, Migration::PENDING)->A;
    $obsolete = PA ($all)->findAll (Migration::status, Migration::OBSOLETE)->orderBy (Migration::date, SORT_DESC)->A;

    // SIMULATE

    if ($pretend) {
      $sql = $this->pretendRollBackMigrations ($obsolete);

      foreach ($pending as $migration) {
        $path = $migration[Migration::path];
        $sql .= $this->formatQueryBlock ($migration[Migration::name], $this->extractMigrationSQL ($path));
      }
      return $sql;
    }

    // MIGRATE

    $count = $this->rollBackMigrations ($obsolete);

    foreach ($pending as $migration) {
      $path = $migration[Migration::path];
      $this->runMigrationSQL ($path);
      $migration[Migration::reverse] = $this->extractMigrationSQL ($path, 'down');
      $this->getTable ()->insert (array_only ($migration, [
        Migration::date,
        Migration::module,
        Migration::name,
        Migration::reverse,
      ]));
      ++$count;
    }
    return $count;
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

  function rollBack ($target = null, $date = null, $pretend = false)
  {
    $this->assertModuleIsSet ();
    $all      = $this->getAllMigrations ();
    $done     = PA ($all)->findAll (Migration::status, Migration::DONE)->orderBy (Migration::date, SORT_DESC)->A;
    $obsolete = PA ($all)->findAll (Migration::status, Migration::OBSOLETE)->orderBy (Migration::date, SORT_DESC)->A;

    // SIMULATE

    if ($pretend)
      return $this->pretendRollBackMigrations ($obsolete) . $this->pretendRollBackMigrations ($done);

    // ROLL BACK

    return $this->rollBackMigrations ($obsolete) + $this->rollBackMigrations ($done);
  }

  function seed ($seeder = 'Seeder')
  {
    $this->assertModuleIsSet ();

  }

  function status ($onlyPending = false)
  {
    $this->assertModuleIsSet ();
    $all = $this->getAllMigrations ();
    return $onlyPending ? array_findAll ($all, Migration::status, Migration::PENDING) : $all;
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
        $table->string (Migration::date, 14);
        $table->string (Migration::module, 64);
        $table->string (Migration::name, 128);
        $table->string (Migration::reverse, 1024);
        $table->primary (Migration::date);
      });
  }

  private function extractMigrationSQL ($path, $method = 'up')
  {
    $migrator  = $this->loadMigrationClass ($path);
    $queries   = $method == 'up' ? $migrator->getUpQueries () : $migrator->getDownQueries ();
    $queries[] = ''; // Appends a ; to the last query.
    return implode (self::QUERY_DELIMITER, $queries);
  }

  private function formatQueryBlock ($name, $sql)
  {
    $sql = str_replace (self::QUERY_DELIMITER, ";\n", $sql);
    return sprintf ("-- %s\n\n%s", $name, $sql);
  }

  private function getAllMigrations ()
  {
    $files        = $this->getProjectMigrations ();
    $dbMigrations = $this->getLoggedMigrations ();
    $all          = $dbMigrations + $files;
    ksort ($all);
    $migrations = map ($all, function ($mig, $date) use ($files, $dbMigrations) {
      if (isset($files[$date]) && !isset($dbMigrations[$date]))
        $mig[Migration::status] = Migration::PENDING;
      elseif (!isset($files[$date]) && isset($dbMigrations[$date]))
        $mig[Migration::status] = Migration::OBSOLETE;
      else $mig[Migration::status] = Migration::DONE;
      return $mig;
    });
    return $migrations;
  }

  private function getLoggedMigrations ()
  {
    return map ($this->getTable ()->orderBy (Migration::date)->get (), function ($rec, &$i) {
      $i = $rec[Migration::date];
      return $rec;
    });
  }

  /**
   * @return string[]
   */
  private function getProjectMigrations ()
  {
    return FilesystemFlow::glob ("{$this->migrationsPath}/*.php")->keys ()->sort ()->map (function ($path, &$i) {
      $i = str_segmentsFirst (basename ($path), '_');
      return [
        Migration::date   => $i,
        Migration::name   => Migration::nameFromFilename ($path),
        Migration::module => $this->module->name,
        Migration::path   => $path,
      ];
    })->all ();
  }

  /**
   * @return Builder
   */
  private function getTable ()
  {
    $this->databaseAPI->connection ()->setFetchMode (\PDO::FETCH_ASSOC);
    return $this->databaseAPI->table (self::MIGRATIONS_TABLE)->where (Migration::module, $this->module->name);
  }

  /**
   * @param string $path
   * @return MigrationInterface
   */
  private function loadMigrationClass ($path)
  {
    $class = Migration::classFromFilename ($path);
    if (!class_exists ($class, false)) {
      if (!file_exists ($path))
        throw new \RuntimeException("Migration file $path was not found");
      require $path;
      if (!class_exists ($class, false))
        throw new \RuntimeException("Migration file $path doesn't define a class named $class");
    }
    return $this->injector->make ($class);
  }

  private function pretendRollBackMigrations (array $migrations)
  {
    $sql = '';
    foreach ($migrations as $migration) {
      $queries = explode (self::QUERY_DELIMITER, $migration[Migration::reverse]);
      $sql .= $this->formatQueryBlock ($migration[Migration::name], implode (self::QUERY_DELIMITER, $queries));
    }
    return $sql;
  }

  private function rollBackMigrations (array $migrations)
  {
    $count = 0;
    foreach ($migrations as $migration) {
      $queries = explode (self::QUERY_DELIMITER, $migration[Migration::reverse]);
      foreach ($queries as $query)
        if ($query)
          $this->databaseAPI->connection ()->unprepared ($query);
      $this->getTable ()->where (Migration::date, $migration[Migration::date])->delete ();
      ++$count;
    }
    return $count;
  }

  private function runMigrationSQL ($path, $method = 'up')
  {
    $migrator = $this->loadMigrationClass ($path);
    $this->databaseAPI->connection ()->transaction (function () use ($migrator, $method) {
      $migrator->$method ();
    });
  }

}
