<?php

use Electro\Exceptions\Fatal\FileNotFoundException;
use Electro\Plugins\IlluminateDatabase\AbstractSeeder;

class __CLASS__ extends AbstractSeeder
{
  /**
   * @throws FileNotFoundException
   */
  public function run ()
  {
    $directory = __DIR__ . '/__NAME__/';
    $files     = preg_grep ('~\.(csv)$~', array_diff (scandir ($directory), ['..', '.']));

    echo PHP_EOL;
    foreach ($files as $file) {
      if ($this->importCsvFrom ($directory . $file))
        $this->io->writeln ("The seeder file <info>$file</info> was imported");
    }
    $this->io->done ('Done');
  }
}
