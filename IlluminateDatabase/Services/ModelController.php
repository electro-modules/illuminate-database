<?php
namespace Electro\Plugins\IlluminateDatabase\Services;

use Illuminate\Database\Eloquent\Model;
use Electro\Database\Lib\AbstractModelController;
use Electro\Interfaces\SessionInterface;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;

class ModelController extends AbstractModelController
{
  /**
   * @var DatabaseAPI
   */
  private $db;

  public function __construct (SessionInterface $session, DatabaseAPI $db)
  {
    parent::__construct ($session);
    $this->db = $db;
  }

  protected function beginTransaction ()
  {
    $this->db->connection ()->beginTransaction ();
  }

  protected function commit ()
  {
    $this->db->connection ()->commit ();
  }

  function loadData ($collection, $subModelPath = '', $id = null, $primaryKey = 'id')
  {
    // Does nothing; no low-level database access support on this implementation.
  }

  function loadModel ($modelClass, $subModelPath = '', $id = null)
  {
    $id                = $this->requestedId ?: $id;
    $this->requestedId = $id;

    /** @var Model $modelClass */
    $model = exists ($id) ? $modelClass::query ()->findOrFail ($id) : new $modelClass;
    if ($subModelPath === '')
      $this->model = $model;
    else setAt ($this->model, $subModelPath, $model);
    return $model;
  }

  protected function rollback ()
  {
    $this->db->connection ()->rollBack ();
  }

  function save ($model, array $options = [])
  {
    if ($model instanceof Model)
      return $model->save ($options);
    return null;
  }

}
