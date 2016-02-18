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
  protected $database;

  /**
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
   * @param string       $table      A table name.
   * @param array|string $columns    An array of column names, or a string of comma-delimited column names.
   * @param string       $csv        The CSV data.
   * @param string       $connection [optional] A connection name, if you want to use a connection other than the
   *                                 default.
   * @return Builder The builder, for chaining.
   */
  function importCSV ($table, $columns, $csv, $connection = null)
  {
    $b    = $this->onTable ($table);
    $data = self::loadCSV ($columns, $csv);
    $b->insert ($data);
    return $b;
  }

  protected function init ()
  {
    $con            = (new Connection)->getFromEnviroment ();
    $this->database = new Database($con);
  }

  /**
   * Gets an Illuminate database fluent query builder instance.
   *
   * @param string $table      The target table name for the query.
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return Builder
   */
  protected function onTable ($table, $connection = null)
  {
    return $this->database->table ($table, $connection);
  }

  /**
   * Gets an Illuminate database schema builder instance.
   *
   * @param string $connection [optional] A connection name, if you want to use a connection other than the default.
   * @return \Illuminate\Database\Schema\Builder
   */
  protected function schema ($connection = null)
  {
    return $this->database->schema ($connection);
  }

}
