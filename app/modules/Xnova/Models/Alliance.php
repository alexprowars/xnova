<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Alliance[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Alliance findFirst(mixed $parameters = null)
 */
class Alliance extends Model
{
	public $id;
	public $name;
	public $tag;
	public $owner;
	public $create_time;
	public $description;
	public $web;
	public $text;
	public $image;
	public $request;
	public $request_notallow;
	public $owner_range;
	public $ranks;
	public $members;

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

	private $rights = [];
	/**
	 * @var AllianceMember $member
	 */
	public $member;

	public function getSource()
	{
		return DB_PREFIX."alliance";
	}

	public function onConstruct()
	{
		$this->hasOne('id', 'Xnova\Models\AllianceMember', 'a_id', ['alias' => 'member']);

		$this->useDynamicUpdate(true);
	}

	public function beforeSave()
	{
		if (is_array($this->ranks))
			$this->ranks = json_encode($this->ranks);
	}

	public function afterSave()
	{
		$this->getRanks();
	}

	public function getRanks()
	{
		if ($this->ranks == null)
			$this->ranks = '[]';

		$this->ranks = json_decode($this->ranks, true);
	}

	public function setRanks($ranks)
	{
		if (is_array($ranks))
			$this->ranks = json_encode($ranks);
		else
			$this->ranks = $ranks;
	}

	/**
	 * @param int $userId
	 * @return AllianceMember
	 */
	public function getMember ($userId)
	{
		$this->member = $this->getRelated('member', "u_id = ".$userId."");
	}

	public function parseRights ($userId = 0)
	{
		if (!$userId)
			$userId = $this->getDI()->getShared('user')->getId();

		$this->rights =
		[
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

		if ($this->owner == $userId)
		{
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
		}
		elseif ($this->member && $this->member->rank == 0)
		{
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
		}
		elseif (isset($this->ranks[$this->member->rank - 1]))
		{
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

	public function canAccess ($method)
	{
		if (!count($this->rights))
			$this->parseRights();

		return (isset($this->rights[$method]) ? $this->rights[$method] : false);
	}

	public function deleteMember ($userId)
	{
		$db = $this->getDI()->getShared('db');

		$db->updateAsDict('game_planets', ['id_ally' => 0], 'id_owner = '.$userId);
		$db->updateAsDict('game_alliance', ['members' => ($this->members - 1)], 'id = '.$this->id);

		$db->delete('game_alliance_members', 'u_id = ?', [$userId]);

		$db->updateAsDict('game_users', ['ally_id' => 0, 'ally_name' => ''], 'id = '.$userId);
	}

	public function deleteAlly ()
	{
		$db = $this->getDI()->getShared('db');

		$db->updateAsDict('game_planets', ['id_ally' => 0], 'id_ally = '.$this->id);
		$db->updateAsDict('game_users', ['ally_id' => 0, 'ally_name' => ''], 'ally_id = '.$this->id);

		$db->delete('game_alliance', 'id = ?', [$this->id]);
		$db->delete('game_alliance_chat', 'ally_id = ?', [$this->id]);
		$db->delete('game_alliance_members', 'a_id = ?', [$this->id]);
		$db->delete('game_alliance_requests', 'a_id = ?', [$this->id]);
		$db->delete('game_alliance_diplomacy', 'a_id = ? OR d_id = ?', [$this->id, $this->id]);
		$db->delete('game_statpoints', 'stat_type = 2 AND id_owner = ?', [$this->id]);
	}
}