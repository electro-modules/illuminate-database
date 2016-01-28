<?php
namespace Selenia\Plugins\IlluminateDatabase;

use Illuminate\Database\Capsule\Manager;
use PhpKit\Connection;

class Database extends Manager
{
  public function __construct (Connection $connection)
  {
    parent::__construct ();
    $this->addConnection ($connection->getFromEnviroment ()->getProperties ());
    $this->bootEloquent ();
    $this->setAsGlobal ();
  }

}
