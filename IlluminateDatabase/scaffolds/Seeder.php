<?php

use $useClassName;

class $className extends $baseClassName
{
  public function run ()
  {
    $directory = __DIR__ . '/csv/';
    $files     = preg_grep ('~\.(csv)$~', array_diff (scandir ($directory), ['..', '.']));

    echo PHP_EOL;
    foreach ($files as $file) {
      if ($this->importCsvFrom ($directory . $file))
        $this->output->writeln ("The seeder file <info>$file</info> was imported successfully!");
    }
    $this->output->done ('Done', true);
  }
}
