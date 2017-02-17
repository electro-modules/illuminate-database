<?php

namespace Electro\Plugins\IlluminateDatabase;

use Electro\Database\Lib\CsvUtil;
use Electro\Interfaces\ConsoleIOInterface;
use Electro\Interfaces\Migrations\SeederInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSeeder implements SeederInterface
{
  /** @var DatabaseAPI */
  protected $db;
  /** @var ConsoleIOInterface */
  protected $output;
  /** @var OutputInterface */
  private $savedOutput;

  public function __construct (DatabaseAPI $db, ConsoleIOInterface $consoleIO)
  {
    $this->db     = $db;
    $this->output = $consoleIO;
  }

  function getQueries ()
  {
    $this->mute ();
    $queries = map ($this->db->connection ()->pretend (function () {
      $this->run ();
    }), function (array $info) {
      $bindings = $info['bindings'];
      return preg_replace_callback ('/\?/', function () use (&$bindings) {
        $v = current ($bindings);
        next ($bindings);
        return $this->quote ($v);
      }, $info['query']);
    });
    $this->mute (false);
    return $queries;
  }

  public function isMuted ()
  {
    return (bool)$this->savedOutput;
  }

  public function mute ($muted = true)
  {
    if ($muted && !$this->savedOutput) {
      $this->savedOutput = $this->output->getOutput ();
      $this->output->setOutput (new NullOutput);
    }
    elseif (!$muted && $this->savedOutput) {
      $this->output->setOutput ($this->savedOutput);
      $this->savedOutput = null;
    }
  }

  /**
   * Parses CSV-formatted data from a string and inserts it into a database table.
   *
   * Data should be comma-delimited and string may be enclosed on double quotes.
   *
   * @param string|null       $table   Table name. If not specified, the name is derived from the filename, without the
   *                                   .csv extension.
   * @param array|string|null $columns Either:<ul>
   *                                   <li> an array of column names,
   *                                   <li> a string of comma-delimited column names,
   *                                   <li> null (or ommited) to read column names from the first row of data.
   *                                   </ul>
   * @param string            $csv     The CSV data.
   * @return $this For chaining.
   */
  protected function importCsv ($table, $columns, $csv)
  {
    $data  = CsvUtil::parseCSV ($columns, $csv);
    $table = $this->db->table ($table);
    foreach ($data as $row)
      $table->insert ($row);
    return $this;
  }

  /**
   * Loads and parses CSV-formatted data from a file and inserts or updates it on a database table.
   *
   * Data should be comma-delimited and string may be enclosed on double quotes.
   *
   * @param string            $file      The file path.
   * @param string|null       $tableName Table name. If not specified, the name is derived from the filename, without
   *                                     the .csv extension.
   * @param array|string|null $columns   Either:<ul>
   *                                     <li> an array of column names,
   *                                     <li> a string of comma-delimited column names,
   *                                     <li> null (or ommited) to read column names from the first row of data.
   *                                     </ul>
   * @return $this For chaining.
   * @throws \Electro\Exceptions\Fatal\FileNotFoundException
   */
  protected function importCsvFrom ($file, $tableName = null, $columns = null)
  {
    if (!$tableName)
      $tableName = basename ($file, '.csv');
    $schema = $this->db->schema ();
    if (!$schema->hasTable ($tableName))
      $this->output->warn ("Table $tableName does not exist. Skipped.");
    else {
      $data = CsvUtil::loadCSV ($file, $columns);
      foreach ($data as $row) {
        $table = $this->db->table ($tableName);
        if (isset ($row['id']) && $table->find ($row['id']))
          $table->update ($row);
        else
          $table->insert ($row);
      }
    }
    return $this;
  }

  /**
   * Quotes a value for insertion into an SQL query, according to the database engine quoting rules.
   *
   * @param mixed $value
   * @return string
   */
  protected function quote ($value)
  {
    return $this->db->connection ()->getPdo ()->quote ($value);
  }

}
