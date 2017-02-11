<?php

use $useClassName;

class $className extends $baseClassName
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {

    }
}


<?php

use Electro\Plugins\IlluminateDatabase\AbstractSeeder;

class Seeder extends AbstractSeeder
{
  public function run ()
  {
    $directory = __DIR__ . '/csv/';
    $files     = preg_grep ('~\.(csv)$~', array_diff (scandir ($directory), ['..', '.']));

    echo PHP_EOL;
    foreach ($files as $file) {
      $this->importCsvFrom ($directory . $file);
      echo ' == The seeder file ' . $file . ' was imported successfully!' . PHP_EOL;
    }
  }
}
