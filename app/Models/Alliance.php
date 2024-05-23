<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Alliance extends Model
{
	public $timestamps = false;
	protected $table = 'alliances';

	private $rights = [];
	/** @var AllianceMember */
	public $member;

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

	public function getRanks()
	{
		if ($this->ranks == null) {
			$this->ranks = [];
		}
	}

	public function setRanks($ranks)
	{
		if (is_array($ranks)) {
			$this->ranks = json_encode($ranks);
		} else {
			$this->ranks = $ranks;
		}
	}

	public function getMember($userId)
	{
		$this->member = AllianceMember::query()->where('u_id', $userId)->get();
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
			self::PLANET_ACCESS 				=> false,
			self::REQUEST_ACCESS 				=> false,
		];

		if ($this->user_id == $userId) {
			$this->rights[self::CAN_WATCH_MEMBERLIST_STATUS] 	= true;
			$this->rights[self::CAN_WATCH_MEMBERLIST] 			= true;
			$this->rights[self::CHAT_ACCESS] 					= true;
			$this->rights[self::CAN_KICK] 						= true;
			$this->rights[self::CAN_EDIT_RIGHTS] 				= true;
			$this->rights[self::CAN_DELETE_ALLIANCE] 			= true;
			$this->rights[self::CAN_ACCEPT] 					= true;
			$this->rights[self::ADMIN_ACCESS] 					= true;
			$this->rights[self::DIPLOMACY_ACCESS] 				= true;
			$this->rights[self::PLANET_ACCESS] 					= true;
			$this->rights[self::REQUEST_ACCESS] 				= true;
		} elseif ($this->member && $this->member->rank == 0) {
			$this->rights[self::CAN_WATCH_MEMBERLIST_STATUS] 	= false;
			$this->rights[self::CAN_WATCH_MEMBERLIST] 			= false;
			$this->rights[self::CHAT_ACCESS] 					= false;
			$this->rights[self::CAN_KICK] 						= false;
			$this->rights[self::CAN_EDIT_RIGHTS] 				= false;
			$this->rights[self::CAN_DELETE_ALLIANCE] 			= false;
			$this->rights[self::CAN_ACCEPT] 					= false;
			$this->rights[self::ADMIN_ACCESS] 					= false;
			$this->rights[self::DIPLOMACY_ACCESS] 				= false;
			$this->rights[self::PLANET_ACCESS] 					= false;
			$this->rights[self::REQUEST_ACCESS] 				= false;
		} elseif (isset($this->ranks[$this->member->rank - 1])) {
			$this->rights[self::CAN_WATCH_MEMBERLIST_STATUS] 	= ($this->ranks[$this->member->rank - 1][self::CAN_WATCH_MEMBERLIST_STATUS] == 1);
			$this->rights[self::CAN_WATCH_MEMBERLIST] 			= ($this->ranks[$this->member->rank - 1][self::CAN_WATCH_MEMBERLIST] == 1);
			$this->rights[self::CHAT_ACCESS] 					= ($this->ranks[$this->member->rank - 1][self::CHAT_ACCESS] == 1);
			$this->rights[self::CAN_KICK] 						= ($this->ranks[$this->member->rank - 1][self::CAN_KICK] == 1);
			$this->rights[self::CAN_EDIT_RIGHTS] 				= ($this->ranks[$this->member->rank - 1][self::CAN_EDIT_RIGHTS] == 1);
			$this->rights[self::CAN_DELETE_ALLIANCE] 			= ($this->ranks[$this->member->rank - 1][self::CAN_DELETE_ALLIANCE] == 1);
			$this->rights[self::CAN_ACCEPT] 					= ($this->ranks[$this->member->rank - 1][self::CAN_ACCEPT] == 1);
			$this->rights[self::ADMIN_ACCESS] 					= ($this->ranks[$this->member->rank - 1][self::ADMIN_ACCESS] == 1);
			$this->rights[self::DIPLOMACY_ACCESS] 				= ($this->ranks[$this->member->rank - 1][self::DIPLOMACY_ACCESS] == 1);
			$this->rights[self::PLANET_ACCESS] 					= ($this->ranks[$this->member->rank - 1][self::PLANET_ACCESS] == 1);
			$this->rights[self::REQUEST_ACCESS] 				= ($this->ranks[$this->member->rank - 1][self::REQUEST_ACCESS] == 1);
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

	public function deleteAlly()
	{
		User::where('alliance_id', $this->id)
			->update(['alliance_id' => null, 'ally_name' => null]);

		$this->delete();

		Statistic::query()->where('stat_type', 1)
			->where('user_id', null)
			->where('alliance_id', $this->id)->delete();
	}
}
