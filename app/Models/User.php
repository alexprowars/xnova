<?php

namespace Xnova\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Traits\HasRoles;
use Xnova\Mail\UserLostPassword;

/**
 * @property int $id
 * @property $group_id
 * @property $username
 * @property $authlevel
 * @property $onlinetime
 * @property $banned
 * @property $planet_current
 * @property $planet_id
 * @property $race
 * @property $sex
 * @property $ally_id
 * @property $ally_name
 * @property $vacation
 * @property $ip
 * @property $lvl_minier
 * @property $lvl_raid
 * @property $xpminier
 * @property $xpraid
 * @property $credits
 * @property $messages
 * @property $messages_ally
 * @property $avatar
 * @property $raids_win
 * @property $raids_lose
 * @property $raids
 * @property $links
 * @property $bonus
 * @property $bonus_multi
 * @property $refers
 * @property $galaxy
 * @property $system
 * @property $planet
 * @property $rpg_geologue
 * @property $rpg_ingenieur
 * @property $rpg_admiral
 * @property $rpg_constructeur
 * @property $rpg_technocrate
 * @property $rpg_meta
 * @property $rpg_komandir
 * @property $deltime
 *
 * @method static self find(int $id)
 * @method static Builder where($column, $operator = null, $value = null)
 */
class User extends Authenticatable
{
	use HasRoles;
	use CrudTrait;

	public $timestamps = false;
	protected $guarded = [];
	protected $hidden = ['password'];

	public function info()
	{
		return $this->hasOne(Account::class, 'id', 'id');
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
		$info = Account::query()->find($this->id, ['email']);

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
