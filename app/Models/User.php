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

	private $optionsDefault = [
		'bb_parser' 		=> true,
		'planetlist' 		=> false,
		'planetlistselect' 	=> false,
		'chatbox' 			=> true,
		'records' 			=> true,
		'only_available' 	=> false,
		'planet_sort'		=> 0,
		'planet_sort_order'	=> 0,
		'color'				=> 0,
		'timezone'			=> 0,
		'spy'				=> 1,
	];

	protected $guarded = [];
	protected $hidden = ['password'];

	protected function casts(): array
	{
		return [
			'options' => 'array',
		];
	}

	public function info()
	{
		return $this->hasOne(UserDetail::class, 'id', 'id');
	}

	public function shortcuts()
	{
		return $this->hasMany(FleetShortcut::class);
	}

	public function quests()
	{
		return $this->hasMany(UserQuest::class);
	}

	public function statistics()
	{
		return $this->hasOne(Statistic::class)->where('stat_type', 1);
	}

	public function planets()
	{
		return $this->hasMany(Planet::class);
	}

	public function alliance()
	{
		return $this->hasOne(Alliance::class);
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

	public function isOnline()
	{
		return (time() - $this->onlinetime) < 180;
	}

	public function getOptions()
	{
		return array_merge($this->optionsDefault, $this->options ?? []);
	}

	public function getOption($key)
	{
		return ($this->options[$key] ?? ($this->optionsDefault[$key] ?? 0));
	}

	public function setOption($key, $value)
	{
		$options = $this->options ?? [];
		$options[$key] = $value;

		$this->options = $options;
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
		} catch (\Exception) {
		}
	}
}
