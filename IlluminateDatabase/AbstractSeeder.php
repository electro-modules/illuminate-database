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
  protected $io;
  /** @var array */
  protected $options = [];
  /** @var OutputInterface */
  private $savedOutput;

  public function __construct (DatabaseAPI $db, ConsoleIOInterface $consoleIO)
  {
    $this->db = $db;
    $this->io = $consoleIO;
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
      $this->savedOutput = $this->io->getOutput ();
      $this->io->setOutput (new NullOutput);
    }
    elseif (!$muted && $this->savedOutput) {
      $this->io->setOutput ($this->savedOutput);
      $this->savedOutput = null;
    }
  }

  function setOptions (array $options)
  {
    $this->options = $options;
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
   * <p>**Note:** if `$this->options['clear']` is `true`, the table is truncated before data is imported.
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
    $io = $this->io;
    $io->begin ();

    if (!$tableName)
      $tableName = basename ($file, '.csv');
    $schema = $this->db->schema ();
    $io->writeln ("Table <comment>$tableName</comment>")->indent (2);

    if (!$schema->hasTable ($tableName))
      $io->warn ("Does not exist. Skipped.");
    else {
      if (get ($this->options, 'clear')) {
        $io->write ("Clearing data\t");
        $this->db->table ($tableName)->truncate ();
        $io->writeln ('<info>done</info>');
      }
      $io->write ("Seeding\t");

      $data = CsvUtil::loadCSV ($file, $columns);
      foreach ($data as $row) {
        foreach ($row as $k => $v)
          if ($v === '')
            $row[$k] = null;
        $table = $this->db->table ($tableName);
        if (isset ($row['id']) && $table->find ($row['id']))
          $table->update ($row);
        else
          $table->insert ($row);
      }
      $io->writeln ('<info>done</info>');
    }
    $io->indent (0)->nl ()->done ();
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
