<?php
namespace Selenia\Plugins\IlluminateDatabase;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Selenia\Traits\InspectionTrait;

/**
 * @method static Model|Collection find($id,array $columns=['*']) Find a model by its primary key.
 * @method static Model|Collection findOrFail($id,array $columns=['*']) Find a model by its primary key or throw an exception.
 */
class BaseModel extends Model
{
  use InspectionTrait;
  static $INSPECTABLE = ['attributes'];

  public $timestamps = false;
}
