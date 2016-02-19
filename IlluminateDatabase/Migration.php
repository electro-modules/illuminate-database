<?php
namespace Selenia\Plugins\IlluminateDatabase;

use ErrorException;
use Illuminate\Database\Query\Builder;
use Phinx\Migration\AbstractMigration;
use PhpKit\Connection;

/**
 * Integrates Illuminate Database with Phinx migrations to allow the developer to create migrations on Selenia using
 * the Illuminate's schema builder, query builder and Eloquent.
 */
class Migration extends AbstractMigration
{
  /** @var Database */
  protected $db;

  /**
   * Parses CSV-formatted data from a string and returns it as an array of arrays.
   *
   * @param array|string $columns An array of column names, or a string of comma-delimited column names.
   * @param string       $csv     The CSV data.
   * @return array The loaded data.
   */
  static function loadCSV ($columns, $csv)
  {
    if (is_string ($columns)) $columns = explode (',', $columns);
// Use an I/O stream instead of an actual file.
    $handle = fopen ('php://temp/myCSV', 'w+b');

// Write all the data to it
    fwrite ($handle, $csv);

// Rewind for reading
    rewind ($handle);

// use fgetcsv which tends to work better than str_getcsv in some cases
    $data = [];
    $i    = 0;
    try {
      while ($row = fgetcsv ($handle, null, ',', "'")) {
        ++$i;
        $data[] = array_combine ($columns, $row);
      }
      fclose ($handle);
    }
    catch (ErrorException $e) {
      echo "\nInvalid row #$i\n\nColumns:\n";
      var_export ($columns);
      echo "\n\nRow:\n";
      var_export ($row);
      echo "\n";
      exit (1);
    }
    return $data;
  }

  /**
   * Returns an instance of the Illuminate Database query builder.
   *
   * @param string $table      Table name,
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Query\Builder
   */
  protected function fromTable ($table, $connection = null)
  {
    return $this->db->fromTable ($table, $connection);
  }

  /**
   * Returns an instance of the Illuminate Database query builder.
   *
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Query\Builder
   */
  protected function getQuery ($connection = null)
  {
    return $this->db->getQuery ($connection);
  }

  /**
   * Returns an instance of the Illuminate Database schema builder.
   *
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Schema\Builder
   */
  protected function getSchema ($connection = null)
  {
    return $this->db->getSchema ($connection);
  }

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
    $data  = self::loadCSV ($columns, $csv);
    $table = $this->db->table ($table, $connection);
    $table->insert ($data);
    return $table;
  }

  protected function init ()
  {
    $con      = (new Connection)->getFromEnviroment ();
    $this->db = new Database($con);
  }


}
