<?php
namespace Electro\Plugins\IlluminateDatabase;

use Phinx\Migration\AbstractMigration;
use PhpKit\Connection;

/**
 * Integrates Illuminate Database with Phinx migrations to allow the developer to create migrations on Electro using
 * Illuminate's schema builder, query builder and Eloquent.
 */
class Migration extends AbstractMigration
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
