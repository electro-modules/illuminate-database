<?php

	namespace Electro\Plugins\IlluminateDatabase\Services;

	use Carbon\Carbon;
	use Electro\Interfaces\UserInterface;
	use Electro\Plugins\IlluminateDatabase\BaseModel;

	class User extends BaseModel implements UserInterface
	{
		public $gender = '$USER_THE';
		public $plural = '$USERS';
		public $singular = '$USER';
		public $timestamps = true;

		protected $casts = [
			'active' => 'boolean',
		];
		protected $dates = ['registrationDate', 'lastLogin'];

		function activeField($set = null)
		{
			if (isset($set))
				$this->active = $set;
			return $this->active;
		}

		public function findById($id)
		{
			/** @var static $user */
			$user = static::find($id);
			if ($user) {
				$this->forceFill($user->getAttributes())->syncOriginal();
				$this->exists = true;
				return true;
			}
			return false;
		}

		public function findByName($username)
		{
			/** @var static $user */
			$user = static::where('username', $username)->first();
			if ($user) {
				$this->forceFill($user->getAttributes())->syncOriginal();
				$this->exists = true;
				return true;
			}
			return false;
		}

		public function getRecord()
		{
			return [
				'active' => $this->activeField(),
				'id' => $this->idField(),
				'lastLogin' => $this->lastLoginField(),
				'realName' => $this->realNameField(),
				'registrationDate' => $this->registrationDateField(),
				'role' => $this->roleField(),
				'remember_token' => $this->tokenField(),
				'username' => $this->usernameField(),
				'email' => $this->emailField(),
				'password' => $this->passwordField()
			];
		}

		function getUsers()
		{
			return $this->newQuery()->where('role', '<=', $this->role)->orderBy('username')->get()->all();
		}

		function idField($set = null)
		{
			if (isset($set))
				$this->id = $set;
			return $this->id;
		}

		function lastLoginField($set = null)
		{
			if (isset($set))
				$this->lastLogin = $set;
			return $this->lastLogin;
		}

		function onLogin()
		{
			$this->lastLogin = Carbon::now();
			$this->save();
		}

		function passwordField($set = null)
		{
			if (isset($set))
				$this->password = password_hash($set, PASSWORD_BCRYPT);
			return $this->password;
		}

		function realNameField($set = null)
		{
			if (isset($set))
				return $this->realName = $set;
			return $this->realName ?: ucfirst($this->usernameField());
		}

		function registrationDateField($set = null)
		{
			if (isset($set))
				$this->created_at = $set;
			return $this->created_at;
		}

		function roleField($set = null)
		{
			if (isset($set))
				$this->role = $set;
			return $this->role;
		}

		function tokenField($set = null)
		{
			if (isset($set))
				$this->remember_token = $set;
			return $this->remember_token;
		}

		function usernameField($set = null)
		{
			if (isset($set))
				$this->username = $set;
			return $this->username;
		}

		function verifyPassword($password)
		{
			if ($password == $this->password) {
				// Migrate plain text password to hashed version.
				$this->passwordField($password);
				$this->save();
				return true;
			}
			return password_verify($password, $this->password);
		}

		function findByEmail($email)
		{

			/** @var static $user */
			$user = static::where('email', $email)->first();
			if ($user) {
				$this->forceFill($user->getAttributes())->syncOriginal();
				$this->exists = true;
				return true;
			}
			return false;
		}

		function findByRememberToken($token)
		{
			/** @var static $user */
			$user = static::where('remember_token', $token)->first();
			if ($user) {
				$this->forceFill($user->getAttributes())->syncOriginal();
				$this->exists = true;
				return true;
			}
			return false;
		}

		function emailField($set = null)
		{
			if (isset($set))
				$this->email = $set;
			return $this->email;
		}

		function remove()
		{
			$this->delete();
		}

		function setRecord($data)
		{
			$newPassword = password_hash(get($data, 'password'), PASSWORD_BCRYPT);
			$now = Carbon::now();
			$email = get($data, 'email');
			$realName = get($data, 'realName');
			$token = get($data, 'token');

			$this->email = $email;
			$this->username = $email;
			$this->realName = $realName;
			$this->remember_token = $token;
			$this->password = $newPassword;
			//$this->registrationDate = $now;
			$this->role = UserInterface::USER_ROLE_STANDARD;
			$this->active = 0;
		}

		function resetPassword($newPassword)
		{
			$this->password = $newPassword;
			$this->remember_token = "";
			$this->save();
		}

		function submit(){
			$this->save();
		}
	}
