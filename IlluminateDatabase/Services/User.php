<?php

namespace Electro\Plugins\IlluminateDatabase\Services;

use Carbon\Carbon;
use Electro\Interfaces\UserInterface;
use Electro\Plugins\IlluminateDatabase\BaseModel;
use Electro\Plugins\IlluminateDatabase\Config\IlluminateDatabaseModule;

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

  /**
   * @throws \Auryn\InjectionException
   */
  public static function boot ()
  {
    parent::boot ();

    static::creating (function ($model) {
      $model->registrationDate = $model->freshTimestamp ();
      $model->updatedAt        = $model->freshTimestamp ();
    });

    IlluminateDatabaseModule::getAPI ();
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
    $pass = get ($data, 'password');
    $data = array_merge ($this->getFields (), $data);
    unset ($data['password']);

    if (exists ($pass))
      $data['password'] = password_hash ($pass, PASSWORD_BCRYPT);

    if (array_key_exists ('active', $data)) $this->active = $data['active'];
    if (array_key_exists ('enabled', $data)) $this->enabled = $data['enabled'];
    if (array_key_exists ('realName', $data)) $this->realName = $data['realName'];
    if (array_key_exists ('email', $data)) $this->email = $data['email'];
    if (array_key_exists ('token', $data)) $this->token = $data['token'];
    if (array_key_exists ('username', $data)) $this->username = $data['username'];
    if (array_key_exists ('role', $data)) $this->role = $data['role'];
    if (array_key_exists ('password', $data)) $this->password = $data['password'];
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
