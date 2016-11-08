<?php
namespace Electro\Plugins\IlluminateDatabase;

use Electro\Traits\InspectionTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

  private static function loadRelatedModel (Relation $relation, $pk)
  {
    $class = get_class ($relation->getRelated ());
    return $class::findOrFail ($pk);
  }

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
//        if (is_scalar ($model))
//          $model = self::loadRelatedModel ($relation, $model);
//        if ($relation instanceof HasManyThrough) {
//          if (!$model->push ()) return false;
//        }
//        elseif ($relation instanceof HasOneOrMany) {
//          $fkey = $relation->getPlainForeignKey ();
//          $model->setAttribute ($fkey, $relation->getParentKey ());
//          if ($relation instanceof MorphOneOrMany) {
//            $mt = $relation->getPlainMorphType ();
//            $m  = $relation->getMorphClass ();
//            $model->setAttribute ($mt, $m);
//          }
//          $model->push ();
//          if (!$relation->save ($model)) return false;
//        }
//        elseif ($relation instanceof BelongsToMany) {
//          if (!$model->push ()) return false;
//          if (!$model->pivot) {
//            $relation->attach ($model);
//          }
//        }
        $model->push ();
      }
    }

    return true;
  }

  public function setAttribute ($key, $value)
  {
    // Check if key is actually a relationship
    if (method_exists ($this, $key)) {

      // If so, convert scalars and arrays to instances of the correct model
      if (isset($value) && !$value instanceof Model) {

        /** @var Relation $relation */
        $relation = $this->$key();

        // ARRAY VALUE

        if (is_array ($value)) {

          // Indexed array

          if (isset($value[0])) {
            if (is_scalar ($value[0]))
              // Replace array items by the corresponding models
              foreach ($value as &$v)
                $v = self::loadRelatedModel ($relation, $v);
            // Convert the array to a collection
            $value = new Collection($value);
          }

          // Associative array (assume it contains fields of a record)

          else {
            $ins = $relation->getRelated ()->newInstance ($value);

            // If the relationship can have multiple items, create a collection
            if (!$relation instanceof HasOne && !$relation instanceof BelongsTo)
              $value = new Collection ($ins);
            // Otherwise assign the model to the relation
            else $value = $ins;
          }
        }

        // SINGLE VALUE

        // If the relationship can have multiple items, create a collection
        else if (!$relation instanceof HasOne && !$relation instanceof BelongsTo)
          $value = new Collection (self::loadRelatedModel ($relation, $value));
        else $value = self::loadRelatedModel ($relation, $value);

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
