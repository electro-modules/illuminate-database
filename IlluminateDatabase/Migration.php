<?php
namespace Electro\Plugins\IlluminateDatabase;

use Electro\Interfaces\ConsoleIOInterface;
use Electro\Interfaces\Migrations\MigrationInterface;

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
  /** @var ConsoleIOInterface */
  protected $output;

  public function __construct (DatabaseAPI $db, ConsoleIOInterface $consoleIO)
  {
    $this->db     = $db;
    $this->output = $consoleIO;
  }

}
