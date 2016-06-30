<?php
namespace Electro\Plugins\IlluminateDatabase;

use Electro\Database\Lib\CsvUtil;
use Phinx\Seed\AbstractSeed;

class AbstractSeeder extends AbstractSeed
{
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
    $data = CsvUtil::parseCSV ($columns, $csv);
    $this->table ($table)->insert ($data)->save ();
    return $this;
  }

  /**
   * Loads and parses CSV-formatted data from a file and inserts it into a database table.
   *
   * Data should be comma-delimited and string may be enclosed on double quotes.
   *
   * @param string            $file    The file path.
   * @param string|null       $table   Table name. If not specified, the name is derived from the filename, without the
   *                                   .csv extension.
   * @param array|string|null $columns Either:<ul>
   *                                   <li> an array of column names,
   *                                   <li> a string of comma-delimited column names,
   *                                   <li> null (or ommited) to read column names from the first row of data.
   *                                   </ul>
   * @return $this For chaining.
   */
  protected function importCsvFrom ($file, $table = null, $columns = null)
  {
    if (!$table)
      $table = basename ($file, '.csv');
    $data = CsvUtil::loadCSV ($file, $columns);
    $this->table ($table)->insert ($data)->save ();
    return $this;
  }
}
