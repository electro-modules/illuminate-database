<?php
namespace Selenia\Plugins\IlluminateDatabase;

use Illuminate\Database\Capsule\Manager;
use PhpKit\ConnectionInterface;

class Database extends Manager
{
  public function __construct (ConnectionInterface $connection)
  {
    parent::__construct ();
    $this->addConnection ($connection->getProperties ());
    $this->bootEloquent ();
    $this->setAsGlobal ();
  }

}
