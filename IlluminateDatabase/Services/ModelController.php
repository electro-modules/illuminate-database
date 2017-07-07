<?php
namespace Electro\Plugins\IlluminateDatabase\Services;

use Electro\Database\Lib\AbstractModelController;
use Electro\Interfaces\SessionInterface;
use Electro\Plugins\IlluminateDatabase\DatabaseAPI;
use Illuminate\Database\Eloquent\Model;

/**
 * A Model Controller that handles Eloquent models.
 *
 * <p>Nested (sub)models are fully supported if the model classes define all the required relations between then.
 */
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

  /**
   * {@inheritdoc}<br>
   * <p>**Note:** For Eloquent models, a primary key name should not be specified; the model's key will be used.
   */
  function loadModel ($modelClass, $subModelPath = '', $id = null, $primaryKey = null)
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

  /**
   * {@inheritdoc}<br>
   * <p>**Note:** For Eloquent models, a primary key name should not be specified; the model's key will be used.
   */
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
