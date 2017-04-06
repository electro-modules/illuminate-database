<?php
namespace Electro\Plugins\IlluminateDatabase\Services;

use Electro\Database\Lib\AbstractModelController;
use Electro\Interfaces\SessionInterface;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Illuminate\Database\Eloquent\Model;

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

  function loadData ($collection, $subModelPath = '', $id = null, $primaryKey = 'id')
  {
    // Does nothing; no low-level database access support on this implementation.
  }

  function loadModel ($modelClass, $subModelPath = '', $id = null)
  {
    $id                = $id ?: $this->requestedId;
    $this->requestedId = $id;

    /** @var Model $modelClass */
    $model = exists ($id) ? $modelClass::query ()->findOrFail ($id) : new $modelClass;
    if ($subModelPath === '')
      $this->model = $model;
    else setAt ($this->model, $subModelPath, $model);
    return $model;
  }

  function save ($model)
  {
    if ($model instanceof Model)
      return $model->push ();
    return null;
  }

  function withRequestedId ($routeParam = 'id', $primaryKey = null)
  {
    $this->requestedId = $this->request->getAttribute ("@$routeParam");
    if (isset($primaryKey))
      throw new \RuntimeException ("A primary key name should not be specified; the model's key will be used.");
    return $this;
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
