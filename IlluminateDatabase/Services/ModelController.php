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

  function loadModel ($modelClassOrCollection, $subModelPath = '', $id = null, $primaryKey = null)
  {
    $id                = $id ?: $this->requestedId;
    $this->requestedId = $id;

    if (!class_exists ($modelClassOrCollection))
      throw new \RuntimeException ("The current Model Controller only supports Eloquent models.");

    /** @var Model $modelClassOrCollection */
    $model = exists ($id) ? $modelClassOrCollection::query ()->findOrFail ($id) : new $modelClassOrCollection;
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
