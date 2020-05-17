<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @property $id
 * @property $name
 * @property $tag
 * @property $owner
 * @property $create_time
 * @property $description
 * @property $web
 * @property $text
 * @property $image
 * @property $request
 * @property $request_notallow
 * @property $owner_range
 * @property $ranks
 * @property $members
 */
class Alliance extends Model
{
	public $timestamps = false;

	private $rights = [];
	/** @var AllianceMember */
	public $member;

	const CAN_WATCH_MEMBERLIST_STATUS = 'onlinestatus';
	const CAN_WATCH_MEMBERLIST = 'memberlist';
	const CHAT_ACCESS = 'chat';
	const CAN_KICK = 'kick';
	const CAN_EDIT_RIGHTS = 'rights';
	const CAN_DELETE_ALLIANCE = 'delete';
	const CAN_ACCEPT = 'accept';
	const ADMIN_ACCESS = 'admin';
	const DIPLOMACY_ACCESS = 'diplomacy';
	const PLANET_ACCESS = 'planet';
	const REQUEST_ACCESS = 'request';

	public function getRanks()
	{
		if ($this->ranks == null) {
			$this->ranks = '[]';
		}

		$this->ranks = json_decode($this->ranks, true);
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
			$userId = Auth::user()->getId();
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

		if ($this->owner == $userId) {
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

		return (isset($this->rights[$method]) ? $this->rights[$method] : false);
	}

	public function deleteMember($userId)
	{
		Alliance::query()->where('id', $this->id)->update(['members' => $this->members - 1]);
		AllianceMember::query()->where('u_id', $userId)->delete();

		Planet::query()->where('id_owner', $userId)->update(['id_ally' => 0]);
		User::query()->where('id', $userId)->update(['ally_id' => 0, 'ally_name' => '']);
	}

	public function deleteAlly()
	{
		User::query()->where('ally_id', $this->id)->update(['ally_id' => 0, 'ally_name' => '']);

		$this->delete();

		AllianceChat::query()->where('ally_id', $this->id)->delete();
		AllianceMember::query()->where('a_id', $this->id)->delete();
		AllianceRequest::query()->where('a_id', $this->id)->delete();
		AllianceDiplomacy::query()->where('a_id', $this->id)->orWhere('d_id', $this->id)->delete();
		Statistic::query()->where('stat_type', 1)->where('id_owner', $this->id)->delete();
	}
}
