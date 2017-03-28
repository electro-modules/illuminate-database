<?php
namespace Electro\Plugins\IlluminateDatabase;

use Electro\Database\Lib\DebugPDO;
use Illuminate\Database\Connectors\ConnectorInterface;
use PhpKit\ExtPDO\ExtPDO;

/**
 * An adapter class that helps integrating ExtPDO with Illuminate Database.
 */
class ElectroConnector implements ConnectorInterface
{
  /** @var bool */
  private $debug;

  public function __construct ($debug = false)
  {
    $this->debug = $debug;
  }

  /**
   * Establish a database connection.
   *
   * @param  array $config
   * @return \PDO
   */
  public function connect (array $config)
  {
    $pdo = $config['pdo'];
    if ($pdo)
      return $pdo;
    $pdo = ExtPDO::create ($config['driver'], $config);
    return $this->debug ? new DebugPDO ($pdo) : $pdo;
  }

}
