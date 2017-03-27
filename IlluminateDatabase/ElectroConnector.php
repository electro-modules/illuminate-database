<?php
namespace Electro\Plugins\IlluminateDatabase;

use Illuminate\Database\Connectors\ConnectorInterface;
use PhpKit\ExtPDO\ExtPDO;

/**
 * An adapter class that helps integrating ExtPDO with Illuminate Database.
 */
class ElectroConnector implements ConnectorInterface
{
  /**
   * Establish a database connection.
   *
   * @param  array $config
   * @return \PDO
   */
  public function connect (array $config)
  {
    /** @var ExtPDO $extPdo */
    $extPdo = $config['pdo'];
    return $extPdo;
  }

}
