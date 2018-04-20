<?php

namespace Electro\Plugins\IlluminateDatabase\Services;

use Carbon\Carbon;
use Electro\Interfaces\UserInterface;
use Electro\Plugins\IlluminateDatabase\BaseModel;

class User extends BaseModel implements UserInterface
{
  const CREATED_AT = 'registrationDate';
  const UPDATED_AT = 'updatedAt';
  public $gender     = '$USER_THE';
  public $plural     = '$USERS';
  public $singular   = '$USER';
  public $timestamps = true;
  protected $casts = [
    'active'  => 'boolean',
    'enabled' => 'boolean',
  ];
  protected $dates = ['registrationDate', 'lastLogin'];

  public static function boot ()
  {
    parent::boot ();

    static::creating (function ($model) {
      $model->registrationDate = $model->freshTimestamp ();
      $model->updatedAt        = $model->freshTimestamp ();
    });
  }

  public function findByEmail ($email)
  {

    /** @var static $user */
    $user = static::where ('email', $email)->first ();
    if ($user) {
      $this->forceFill ($user->getAttributes ())->syncOriginal ();
      $this->exists = true;
      return true;
    }
    return false;
  }

  public function findById ($id)
  {
    /** @var static $user */
    $user = static::find ($id);
    if ($user) {
      $this->forceFill ($user->getAttributes ())->syncOriginal ();
      $this->exists = true;
      return true;
    }
    return false;
  }

  public function findByName ($username)
  {
    /** @var static $user */
    $user = static::where ('username', $username)->first ();
    if ($user) {
      $this->forceFill ($user->getAttributes ())->syncOriginal ();
      $this->exists = true;
      return true;
    }
    return false;
  }

  public function findByToken ($token)
  {
    /** @var static $user */
    $user = static::where ('token', $token)->first ();
    if ($user) {
      $this->forceFill ($user->getAttributes ())->syncOriginal ();
      $this->exists = true;
      return true;
    }
    return false;
  }

  public function getFields ()
  {
    return [
      'active'           => $this->active,
      'id'               => $this->id,
      'lastLogin'        => $this->lastLogin,
      'realName'         => $this->realName,
      'registrationDate' => $this->registrationDate,
      'updatedAt'        => $this->updatedAt,
      'role'             => $this->role,
      'token'            => $this->token,
      'username'         => $this->username,
      'email'            => $this->email,
      'password'         => '',
      'enabled'          => $this->enabled,
    ];
  }

  function getUsers ()
  {
    return $this->newQuery ()->where ('role', '<=', $this->role)->orderBy ('username')->get ()->all ();
  }

  function mergeFields ($data)
  {
    if (exists (get ($data, 'password'))) {
      $this->password = password_hash (get ($data, 'password'), PASSWORD_BCRYPT);
    }

    $this->active   = get ($data, 'active', 0);
    $this->enabled  = get ($data, 'enabled', 1);
    $this->realName = get ($data, 'realName');
    $this->email    = get ($data, 'email');
    $this->token    = get ($data, 'token');
    $this->username = get ($data, 'username');
    $this->role     = get ($data, 'role', UserInterface::USER_ROLE_STANDARD);
  }

  function onLogin ()
  {
    $this->lastLogin = Carbon::now ();
    $this->submit ();
  }

  function remove ()
  {
    $this->delete ();
  }

  function submit ()
  {
    $this->save ();
  }

  function verifyPassword ($password)
  {
    if ($password == $this->password) {
      return true;
    }
    return password_verify ($password, $this->password);
  }
}
