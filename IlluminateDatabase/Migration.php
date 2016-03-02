<?php
namespace Selenia\Plugins\IlluminateDatabase;

use Illuminate\Database\Query\Builder;
use Phinx\Migration\AbstractMigration;
use PhpKit\Connection;
use Selenia\Database\Lib\DbUtil;

/**
 * Integrates Illuminate Database with Phinx migrations to allow the developer to create migrations on Selenia using
 * Illuminate's schema builder, query builder and Eloquent.
 */
class Migration extends AbstractMigration
{
  /** @var DatabaseAPI */
  protected $db;

  /**
   * Imports data from a CSV-formatted string into a database table.
   *
   * @param string       $table      A table name.
   * @param array|string $columns    An array of column names, or a string of comma-delimited column names.
   * @param string       $csv        The CSV data.
   * @param string       $connection [optional] A connection name, if you want to use a connection other than the
   *                                 default.
   * @return Builder The builder, for chaining.
   */
  protected function importCSV ($table, $columns, $csv, $connection = null)
  {
    $data  = DbUtil::loadCSV ($columns, $csv);
    $table = $this->db->table ($table, $connection);
    $table->insert ($data);
    return $table;
  }

  protected function init ()
  {
    $con      = (new Connection)->getFromEnviroment ();
    $this->db = new DatabaseAPI ($con);
    $this->db->manager->setAsGlobal ();
    $this->db->manager->bootEloquent ();
  }


}
