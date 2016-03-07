<?php
namespace Selenia\Plugins\IlluminateDatabase\Services;

use Illuminate\Database\Eloquent\Model;
use Selenia\Database\Services\ModelController as BaseModelConroller;
use Selenia\Interfaces\InjectorInterface;
use Selenia\Interfaces\SessionInterface;
use Selenia\Plugins\IlluminateDatabase\DatabaseAPI;

class ModelController extends BaseModelConroller
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

  function loadModel ($modelClass, $path = '', $id = null)
  {
    /** @var Model $modelClass */
    $model = exists ($id) ? $modelClass::query ()->findOrFail ($id) : new $modelClass;
    if ($path === '')
      $this->model = $model;
    else setAt ($this->model, $path, $model);
    return $model;
  }

  function loadRequested ($modelClass, $modelName = '', $param = 'id')
  {
    return $this->loadModel ($modelClass, $modelName, $this->request->getAttribute ("@$param"));
  }

  function save ($model, array $options = [])
  {
    if ($model instanceof Model)
      return $this->model->save ($options);
    return false;
  }

  protected function beginTransaction ()
  {
    $this->db->connection ()->beginTransaction ();
  }

  protected function commit ()
  {
    $this->db->connection ()->commit ();
  }

  protected function rollback ()
  {
    $this->db->connection ()->rollBack ();
  }

}
