<?php
namespace Selenia\Plugins\IlluminateDatabase\Services;

use Illuminate\Database\Eloquent\Model;
use Selenia\Database\Services\ModelController as OriginalConroller;
use Selenia\Interfaces\InjectorInterface;
use Selenia\Interfaces\SessionInterface;
use Selenia\Plugins\IlluminateDatabase\DatabaseAPI;

class ModelController extends OriginalConroller
{
  /**
   * @var DatabaseAPI
   */
  private $db;

  public function __construct (InjectorInterface $injector, SessionInterface $session, DatabaseAPI $db)
  {
    parent::__construct ($injector, $session);
    $this->db = $db;
  }

  function loadRequested ($modelClass, $param = 'id')
  {
    $id = $this->request->getAttribute ("@$param");
    if (!$id) return new $modelClass;
    /** @var Model $modelClass */
    return $modelClass::query ()->findOrFail ($id);
  }

  function saveModel ()
  {
    if (!$this->model instanceof Model)
      return parent::saveModel ();

    $this->db->connection ()->beginTransaction ();
    try {
      $this->callEventHandlers ($this->preSaveHandlers);
      $s = $this->model->save ();
      $this->callEventHandlers ($this->postSaveHandlers);
      $this->db->connection ()->commit ();
      return $s;
    }
    catch (\Exception $e) {
      $this->db->connection ()->rollBack ();
      throw $e;
    }
  }

}
