<?php
namespace Electro\Plugins\IlluminateDatabase;

use Electro\Interfaces\Migrations\MigrationInterface;
use PhpKit\Connection;

/**
 * Provides a base class for migrations that use the Illuminate Database library.
 *
 * <p>It allows the developer to create migrations on Electro using Illuminate's schema builder, query builder and
 * Eloquent.
 */
abstract class Migration implements MigrationInterface
{
  /** @var DatabaseAPI */
  protected $db;

  protected function init ()
  {
    $con      = (new Connection)->getFromEnviroment ();
    $this->db = new DatabaseAPI ($con);
    $this->db->manager->setAsGlobal ();
    $this->db->manager->bootEloquent ();
  }

}
