<?php

namespace Electro\Plugins\IlluminateDatabase\Commands;

use Electro\ConsoleApplication\Lib\ModulesUtil;
use Electro\ConsoleApplication\Services\ConsoleIO;
use Electro\Exceptions\Fatal\FileNotFoundException;
use Electro\Interfaces\Migrations\MigrationsInterface;
use Electro\Interop\MigrationStruct;
use Electro\Interop\MigrationStruct as Migration;
use Electro\Kernel\Lib\ModuleInfo;
use Electro\Kernel\Services\ModulesInstaller;
use Electro\Kernel\Services\ModulesRegistry;
use Electro\Plugins\IlluminateDatabase\Config\MigrationsSettings;
use Robo\Task\File\Replace;
use Robo\Task\FileSystem\FilesystemStack;

/**
 * Database migration commands.
 */
class MigrationCommands
{
  /**
   * @var FilesystemStack
   */
  private $fs;
  /**
   * @var ConsoleIO
   */
  private $io;
  /**
   * @var MigrationsInterface
   */
  private $migrationsAPI;
  /**
   * @var ModulesInstaller
   */
  private $modulesInstaller;
  /**
   * @var ModulesUtil
   */
  private $modulesUtil;
  /**
   * @var ModulesRegistry
   */
  private $registry;
  /**
   * @var MigrationsSettings
   */
  private $settings;

  function __construct (MigrationsSettings $settings, ConsoleIO $io, ModulesUtil $modulesUtil,
                        MigrationsInterface $migrationsAPI, FilesystemStack $fs,
                        ModulesRegistry $registry, ModulesInstaller $modulesInstaller)
  {
    $this->io               = $io;
    $this->modulesUtil      = $modulesUtil;
    $this->settings         = $settings;
    $this->migrationsAPI    = $migrationsAPI;
    $this->fs               = $fs;
    $this->registry         = $registry;
    $this->modulesInstaller = $modulesInstaller;
  }

  /**
   * Create a new database migration
   *
   * @param string $moduleName [optional] The target module (vendor-name/package-name syntax).
   *                           If not specified, the user will be prompted for it
   * @param string $name       [optional] The name of the migration (a human-friendly description; it may contain
   *                           spaces, but not accented characters). If not specified, the user will be prompted for it
   * @param array  $options
   * @option $class|l Do not generate a class name; use the provided one
   * @option $template|t Use an alternative template
   * @option $doc|d Generate embedded documentation.
   * @return int Status code
   */
  function makeMigration ($moduleName = null, $name = null, $options = [
    'class|l'    => null,
    'template|t' => null,
    'doc|d'      => false,
  ])
  {
    $io = $this->io;
    $this->setupModule ($moduleName);
    if (!$name) {
      $name = $io->ask ('Migration description:');
      if (!$name)
        $io->cancel ();
    }
    $doc       = get ($options, 'doc');
    $className = get ($options, 'class') ?: str_camelize ($name, true);
    $template  = get ($options, 'template') ?: $doc ? 'IlluminateMigration-doc.php' : 'IlluminateMigration.php';

    $module     = $this->registry->getModule ($moduleName);
    $srcPath    = sprintf ('%s/scaffolds/%s', dirname (__DIR__), $template);
    $filename   = sprintf ('%s_%s.php', strftime ('%Y%m%d%H%M%S'), str_decamelize ($className, false, '_'));
    $targetPath = "$module->path/{$this->settings->migrationsPath()}/$filename";

    $io->mute ();
    $this->fs->copy ($srcPath, $targetPath)->run ();

    (new Replace ($targetPath))
      ->from ([
        '__CLASS__',
      ])
      ->to ([
        $className,
      ])
      ->run ();

    $io->unmute ();
    $io->done ("Migration <info>$filename</info> was created");
  }

  /**
   * Create a new database seeder
   *
   * @param string $moduleName [optional] The target module (vendor-name/package-name syntax).
   *                           If not specified, the user will be prompted for it
   * @param string $name       [optional] The name of the seeder (a human-friendly description, it may contain
   *                           spaces, but not accented characters). If not specified, the user will be prompted for it
   * @return int Status code
   */
  function makeSeeder ($moduleName = null, $name = null)
  {
    $this->setupModule ($moduleName);

    return 0;
  }

  /**
   * Runs all pending migrations of a module, optionally up to a specific version
   *
   * @param string $moduleName [optional] The target module (vendor-name/package-name syntax).
   *                           If not specified, the user will be prompted for it
   * @param array  $options
   * @option $target|t         The upper limit timestamp to migrate up to (ex: 20170314150049), inclusive; migrations
   *                           with timestamps greater than it will be excluded; if not specified, it runs up to the
   *                           most recent pending migration
   * @option $pretend|p        Do not actually run the migration, just output the SQL code that would be executed
   * @return int Status code
   */
  function migrate ($moduleName = null, $options = [
    'target|t'  => null,
    'pretend|p' => false,
  ])
  {
    $this->setupModule ($moduleName);
    $pretend = get ($options, 'pretend');
    $out     = $this->migrationsAPI->migrate (get ($options, 'target'), $pretend);
    if ($pretend)
      $this->io->writeln ($out);
    else $out
      ? $this->io->done (sprintf ('<info>%d %s ran successfully</info>', $out, simplePluralize ($out, 'migration')))
      : $this->io->warn ("Nothing to migrate")->done ();
    return 0;
  }

  /**
   * Reset and re-run all migrations
   *
   * @param string $moduleName [optional] The target module (vendor-name/package-name syntax).
   *                           If not specified, the user will be prompted for it
   * @param array  $options
   * @option $seed When specified, runs the seeder after the migration
   * @option $seeder|s The name of the seeder (in camel case)
   * @return int Status code
   */
  function migrateRefresh ($moduleName = null, $options = [
    '--seed'   => false,
    'seeder|s' => 'Seeder',
  ])
  {
    $this->setupModule ($moduleName);
    $r = $this->migrateReset ($moduleName);
    if ($r) return $r;
    $r = $this->migrate ($moduleName);
    if ($r) return $r;
    if (get ($options, 'seed'))
      return $this->migrateSeed ($moduleName, $options);
    return 0;
  }

  /**
   * Rollback all database migrations
   *
   * @param string $moduleName [optional] The target module (vendor-name/package-name syntax).
   *                           If not specified, the user will be prompted for it
   * @return int Status code
   */
  function migrateReset ($moduleName = null)
  {
    $this->setupModule ($moduleName);
    return $this->migrateRollback ($moduleName, ['target' => 0]);
  }

  /**
   * Reverts the last migration of a specific module, or optionally up to a specific version
   *
   * @param string $moduleName [optional] The target module (vendor-name/package-name syntax).
   *                           If not specified, the user will be prompted for it
   * @param array  $options
   * @option $target|t         The lower limit timestamp to rollback to (ex: 20170314150049), inclusive; migrations
   *                           with timestamps lower than it will be excluded; 0 = roll back all migrations.
   *                           If not given, the last migration will be rolled back.
   * @option $pretend|p        Do not actually run the migration, just output the SQL code that would be executed
   * @return int Status code
   */
  function migrateRollback ($moduleName = null, $options = [
    'target|t'  => null,
    'pretend|p' => false,
  ])
  {
    $target = get ($options, 'target');
    $this->setupModule ($moduleName);
    $pretend = get ($options, 'pretend');
    $out     = $this->migrationsAPI->rollBack ($target, $pretend);
    if ($pretend)
      $this->io->writeln ($out);
    else $out
      ? $this->io->done (sprintf ('<info>%d %s rolled back successfully</info>', $out,
        simplePluralize ($out, 'migration')))
      : $this->io->warn ("Nothing to roll back")->done ();
    return 0;
  }

  /**
   * Run all available seeders of a specific module, or just a specific seeder
   *
   * @param string $moduleName [optional] The target module (vendor-name/package-name syntax).
   *                           If not specified, the user will be prompted for it
   * @param array  $options
   * @option $seeder|s The name of the seeder (in camel case)
   * @option $pretend|p Do not actually run the seeder, just output the SQL code that would be executed
   * @option $clear|c Truncate tables before importing data into them
   * @return int Status code
   */
  function migrateSeed ($moduleName = null, $options = [
    'seeder|s'  => 'Seeder',
    'pretend|p' => false,
    'clear|c'   => false,
  ])
  {
    $this->setupModule ($moduleName);
    $pretend = get ($options, 'pretend');
    try {
      $out = $this->migrationsAPI->seed (get ($options, 'seeder'), $options);
    }
    catch (FileNotFoundException $e) {
      $this->io->error ($e->getMessage ());
    }
    if ($pretend)
      $this->io->writeln ($out);
    else $out
      ? $this->io->done (sprintf ('<info>%d %s ran successfully</info>', $out, simplePluralize ($out, 'seeder')))
      : $this->io->warn ("Nothing to seed")->done ();
    return 0;
  }

  /**
   * Print a list of all migrations of a specific module, along with their current status
   *
   * @param string $moduleName [optional] The target module (vendor-name/package-name syntax).
   *                           If not specified, the user will be prompted for it
   * @param array  $options
   * @option $format|f      The output format. Allowed values: 'json|text'. If not specified, text is output.
   * @return int Status code
   */
  function migrateStatus ($moduleName = null, $options = [
    'format|f' => 'text',
  ])
  {
    $this->setupModule ($moduleName);
    $migrations   = $this->migrationsAPI->status ();
    $formattedMig = map ($migrations, function ($mig) {
      return [
        $mig[Migration::date],
        Migration::toDateStr ($mig[Migration::date]),
        $mig[Migration::name],
        $mig[Migration::status],
      ];
    });
    switch (get ($options, 'format', 'text')) {
      case 'json':
        $this->io->writeln (json_encode (array_values ($migrations), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        break;
      case 'text':
        if ($formattedMig)
          $this->io->table (['Version', 'Date', 'Name', 'Status'], $formattedMig, [15, 20, 40, 8]);
        else $this->io->cancel ('The module has no migrations');
        break;
      default:
        $this->io->error ('Invalid format');
    }
    return 0;
  }

  private function formatCount ($v, $tag = 'info')
  {
    return !$v ? '  -' : sprintf ("<$tag>%s</$tag>", str_pad ($v, 3, ' ', STR_PAD_LEFT));
  }

  /**
   * Prepares the migrations context for running on the specified module.
   *
   * It also validates the module name and/or asks for it, if empty. In the later case, the `$moduleName` argument will
   * be updated on the caller.
   *
   * @param string $moduleName vendor-name/package-name
   */
  private function setupModule (&$moduleName)
  {
    $this->modulesUtil->selectInstalledModule ($moduleName,
      function (ModuleInfo $module) { return $module->enabled; },
      false,
      function ($moduleName) {
        $migrations = $this->modulesInstaller->getMigrationsOf ($moduleName);
        $pending    = array_findAll ($migrations, MigrationStruct::status, MigrationStruct::PENDING);
        return sprintf (' <comment>migrations:</comment> %s  <comment>pending:</comment> %s',
          $this->formatCount (count ($migrations)),
          $this->formatCount (count ($pending))
        );
      });
    $this->migrationsAPI->module ($moduleName);
  }
}
