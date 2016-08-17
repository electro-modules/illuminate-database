<?php
namespace Electro\Plugins\IlluminateDatabase\Services;

use Electro\Core\Assembly\ModuleInfo;
use Electro\Interfaces\MigrationsInterface;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Electro\Plugins\IlluminateDatabase\Migrations\Config\MigrationsSettings;
use Illuminate\Database\Schema\Blueprint;

class Migrations implements MigrationsInterface
{
  const MIGRATIONS_TABLE = 'migrations';
  /**
   * @var DatabaseAPI
   */
  private $databaseAPI;
  /**
   * @var MigrationsSettings
   */
  private $settings;

  public function __construct (DatabaseAPI $databaseAPI, MigrationsSettings $settings) {
    $this->databaseAPI = $databaseAPI;
    $this->settings = $settings;
  }

  private function ensureMigrationsTableExists ()
  {
    $schema = $this->databaseAPI->schema();
    if (!$schema->hasTable(self::MIGRATIONS_TABLE))
      $schema->create(self::MIGRATIONS_TABLE, function (Blueprint $table) {
        $table->increments('id');
        $table->dateTime('');
      });
  }

  function migrate ($moduleName, $target = null)
  {

  }

  function reset ($moduleName)
  {
    // TODO: Implement reset() method.
  }

  function rollback ($moduleName, $target = null, $date = null)
  {
    // TODO: Implement rollback() method.
  }

  function seed ($moduleName, $seeder = null)
  {
    // TODO: Implement seed() method.
  }

  function status ($moduleName, $onlyPending = false)
  {
    // TODO: Implement status() method.
  }

  /**
   * @param string|ModuleInfo $module
   * @return bool
   */
  private function moduleHasMigrations ($module)
  {
    if (is_string ($module))
      $module = $this->registry->getModule ($module);
    $path = "$module->path/" . $this->migrationsSettings->migrationsPath ();
    return fileExists ($path);
  }

}
