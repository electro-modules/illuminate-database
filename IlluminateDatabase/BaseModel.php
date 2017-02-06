<?php
namespace Electro\Plugins\IlluminateDatabase;

use Electro\Traits\InspectionTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @method static Model|Collection find($id, array $columns = ['*']) Find a model by its primary key.
 * @method static Model|Collection findOrFail($id, array $columns = ['*']) Find a model by its primary key or throw an
 *         exception.
 */
class BaseModel extends Model
{
  use InspectionTrait;
  static $INSPECTABLE = ['attributes'];

  public $timestamps = false;

  public function push ()
  {
    // Before we save, save any parent entities
    foreach ($this->relations as $name => $model) {

      if (!method_exists ($this, $name)) {
        if (!$model->push ()) return false;
        return true;
      }

      // Get relationship type
      $relation = $this->$name();

      if (!($relation instanceof BelongsTo)) {
        continue;
      }

      if (!$model->push ()) return false;

      $relation->associate ($model);
    }

    if (!$this->save ()) return false;

    // Now the other relationships
    /** @var Model $models */
    foreach ($this->relations as $name => $models) {

      if (!method_exists ($this, $name)) {
        if (!$models->push ()) return false;
        return true;
      }

      // Get relationship
      /** @var Relation $relation */
      $relation = $this->$name();

      if ($models instanceof Model)
        $models = [$models];
      /** @var Model $model */
      foreach (Collection::make ($models) as $model) {
        // $model may be a foreign key value submitted by a form
        if (is_scalar ($model)) {
          $value = $model;
          $class = get_class ($relation->getRelated ());
          $model = $class::find ($value)
            ?: $relation->getRelated ()->newInstance ([$relation->getRelated ()->primaryKey => $value]);
        }
        if ($relation instanceof HasManyThrough) {
          if (!$model->push ()) return false;
        }
        elseif ($relation instanceof HasOneOrMany) {
          $fkey = $relation->getForeignKeyName();
          $model->setAttribute ($fkey, $relation->getParentKey ());
          if ($relation instanceof MorphOneOrMany) {
            $mt = $relation->getMorphType ();
            $m  = $relation->getMorphClass ();
            $model->setAttribute ($mt, $m);
          }
          $model->push ();
          if (!$relation->save ($model)) return false;
        }
        elseif ($relation instanceof BelongsToMany) {
          if (!$model->push ()) return false;
          if (!$model->pivot) {
            $relation->attach ($model);
          }
        }
      }
    }

    return true;
  }

  public function setAttribute ($key, $value)
  {
    // Check if key is actually a relationship
    /** @var Relation $relation */
    if (method_exists ($this, $key)) {
      // If so, convert scalars and arrays to instances of the correct model
      if (isset($value) && !$value instanceof Model) {

        /** @var Relation $relation */
        $relation = $this->$key();
        // Convert arrays to instances of the correct model
        if (is_array ($value)) {
          if (isset($value[0]) && is_scalar ($value[0])) {

          }
          else $value = $relation->getRelated ()->newInstance ($value);
        }

        if ($relation instanceof BelongsTo) {
          $relation->associate ($value);
        }
        else {
          $this->setRelation ($key, $value);
        }
        return;
      }
    }
    parent::setAttribute ($key, $value);
  }

}
