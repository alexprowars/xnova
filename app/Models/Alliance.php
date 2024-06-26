<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Alliance extends Model
{
	protected $table = 'alliances';
	protected $guarded = [];

	public $rights = [];
	public ?AllianceMember $member;

	public const CAN_WATCH_MEMBERLIST_STATUS = 'onlinestatus';
	public const CAN_WATCH_MEMBERLIST = 'memberlist';
	public const CHAT_ACCESS = 'chat';
	public const CAN_KICK = 'kick';
	public const CAN_EDIT_RIGHTS = 'rights';
	public const CAN_DELETE_ALLIANCE = 'delete';
	public const CAN_ACCEPT = 'accept';
	public const ADMIN_ACCESS = 'admin';
	public const DIPLOMACY_ACCESS = 'diplomacy';
	public const PLANET_ACCESS = 'planet';
	public const REQUEST_ACCESS = 'request';

	protected function casts(): array
	{
		return [
			'ranks' => 'array',
		];
	}

	public function user()
	{
		return $this->hasOne(User::class);
	}

	public function members()
	{
		return $this->hasMany(AllianceMember::class);
	}

	public function getRanks()
	{
		if (empty($this->ranks)) {
			$this->ranks = [];
		}
	}

	public function getMember($userId)
	{
		$this->member ??= $this->members()->where('user_id', $userId)->first();

		return $this->member;
	}

	public function parseRights($userId = 0)
	{
		if (!$userId && Auth::check()) {
			$userId = Auth::user()->id;
		}

		$this->rights = [
			self::CAN_WATCH_MEMBERLIST_STATUS 	=> false,
			self::CAN_WATCH_MEMBERLIST 			=> false,
			self::CHAT_ACCESS 					=> false,
			self::CAN_KICK 						=> false,
			self::CAN_EDIT_RIGHTS 				=> false,
			self::CAN_DELETE_ALLIANCE 			=> false,
			self::CAN_ACCEPT 					=> false,
			self::ADMIN_ACCESS 					=> false,
			self::DIPLOMACY_ACCESS 				=> false,
			self::REQUEST_ACCESS 				=> false,
		];

		if ($this->user_id == $userId) {
			foreach ($this->rights as $key => $value) {
				$this->rights[$key] = true;
			}
		} elseif (isset($this->ranks[$this->member->rank])) {
			$this->rights[self::CAN_WATCH_MEMBERLIST_STATUS] 	= $this->ranks[$this->member->rank][self::CAN_WATCH_MEMBERLIST_STATUS] == 1;
			$this->rights[self::CAN_WATCH_MEMBERLIST] 			= $this->ranks[$this->member->rank][self::CAN_WATCH_MEMBERLIST] == 1;
			$this->rights[self::CHAT_ACCESS] 					= $this->ranks[$this->member->rank][self::CHAT_ACCESS] == 1;
			$this->rights[self::CAN_KICK] 						= $this->ranks[$this->member->rank][self::CAN_KICK] == 1;
			$this->rights[self::CAN_EDIT_RIGHTS] 				= $this->ranks[$this->member->rank][self::CAN_EDIT_RIGHTS] == 1;
			$this->rights[self::CAN_DELETE_ALLIANCE] 			= $this->ranks[$this->member->rank][self::CAN_DELETE_ALLIANCE] == 1;
			$this->rights[self::CAN_ACCEPT] 					= $this->ranks[$this->member->rank][self::CAN_ACCEPT] == 1;
			$this->rights[self::ADMIN_ACCESS] 					= $this->ranks[$this->member->rank][self::ADMIN_ACCESS] == 1;
			$this->rights[self::DIPLOMACY_ACCESS] 				= $this->ranks[$this->member->rank][self::DIPLOMACY_ACCESS] == 1;
			$this->rights[self::REQUEST_ACCESS] 				= $this->ranks[$this->member->rank][self::REQUEST_ACCESS] == 1;
		}
	}

	public function canAccess($method)
	{
		if (!count($this->rights)) {
			$this->parseRights();
		}

		return $this->rights[$method] ?? false;
	}

	public function deleteMember($userId)
	{
		$this->decrement('members');
		AllianceMember::query()->where('user_id', $userId)->delete();

		Planet::query()->where('user_id', $userId)->update(['alliance_id' => null]);
		User::query()->find($userId)->update(['alliance_id' => null, 'alliance_name' => null]);
	}

	public function delete()
	{
		User::where('alliance_id', $this->id)
			->update(['alliance_id' => null, 'ally_name' => null]);

		parent::delete();

		Statistic::query()->where('stat_type', 1)
			->where('user_id', null)
			->where('alliance_id', $this->id)->delete();
	}
}
