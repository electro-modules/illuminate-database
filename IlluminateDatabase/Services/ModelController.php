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

  function loadModel ($modelClass, $modelName = '', $id = null)
  {
    /** @var Model $modelClass */
    $model = exists ($id) ? $modelClass::query ()->findOrFail ($id) : new $modelClass;
    if ($modelName === '')
      $this->model = $model;
    else setAt ($this->model, $modelName, $model);
    return $model;
  }

  function loadRequested ($modelClass, $modelName = '', $param = 'id')
  {
    return $this->loadModel ($modelClass, $modelName, $this->request->getAttribute ("@$param"));
  }

  function saveModel (array $options = [])
  {
    $this->db->connection ()->beginTransaction ();
    try {
      $this->callEventHandlers ($this->preSaveHandlers);

      $model = $this->model;
      if ($model instanceof Model)
        $s = $this->model->save ($options);
      else $s = $this->saveCompositeModel ($options);

      $this->callEventHandlers ($this->postSaveHandlers);
      $this->db->connection ()->commit ();
      return $s;
    }
    catch (\Exception $e) {
      $this->db->connection ()->rollBack ();
      throw $e;
    }
  }

  /**
   * Saves all elements of the model that are instances of Model.
   *
   * @param array $options
   * @return bool
   */
  protected function saveCompositeModel (array $options = [])
  {
    foreach ($this->model as $submodel)
      if ($submodel instanceof Model)
        if (!$submodel->save ($options))
          return false;
    return true;
  }

}
