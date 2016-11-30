<?php
namespace Electro\Plugins\IlluminateDatabase\Services;

use Exception;

class ExceptionHandler implements \Illuminate\Contracts\Debug\ExceptionHandler
{

  public function report (Exception $e)
  {
    throw $e;
  }

  public function render ($request, Exception $e)
  {
    // Not needed when running on Electro
  }

  public function renderForConsole ($output, Exception $e)
  {
    // Not needed when running on Electro
  }
}
