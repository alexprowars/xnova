<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Traits\HasRoles;
use App\Mail\UserLostPassword;

class User extends Authenticatable
{
	use HasRoles;
	use CrudTrait;

	public $timestamps = false;
	protected $guarded = [];
	protected $hidden = ['password'];

	public function info()
	{
		return $this->hasOne(UserDetail::class, 'id', 'id');
	}

	public function getId(): int
	{
		return (int) $this->id;
	}

	public function isAdmin()
	{
		if ($this->id > 0) {
			return $this->hasRole('admin');
		} else {
			return false;
		}
	}

	public function isVacation()
	{
		return $this->vacation > 0;
	}

	public function getFullName()
	{
		return trim($this->username);
	}

	public function isOnline()
	{
		return (time() - $this->onlinetime < 180);
	}

	public function getEmailForPasswordReset()
	{
		$info = UserDetail::query()->find($this->id, ['email']);

		return $info->email ?? null;
	}

	public function sendPasswordResetNotification($token)
	{
		$email = $this->getEmailForPasswordReset();

		try {
			Mail::to($email)->send(new UserLostPassword([
				'#EMAIL#' => $email,
				'#NAME#' => $this->username,
				'#URL#' => URL::route('login.reset', ['token' => $token, 'user' => $email]),
			]));
		} catch (\Exception $e) {
		}
	}
}
