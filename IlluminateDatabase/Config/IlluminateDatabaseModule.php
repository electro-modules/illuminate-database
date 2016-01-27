<?php
namespace Selenia\Plugins\Config\IlluminateDatabase;

use Selenia\Interfaces\ServiceProviderInterface;
use Selenia\Interfaces\InjectorInterface;
use Selenia\Plugins\IlluminateDatabase\Database;

class IlluminateDatabaseModule implements ServiceProviderInterface
{
  function register (InjectorInterface $injector)
  {
    $injector->share (Database::class);
  }

}
