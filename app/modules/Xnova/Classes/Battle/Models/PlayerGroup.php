<?php

namespace Xnova\Battle\Models;

use Xnova\Battle\CombatObject\FireManager;
use Xnova\Battle\Utils\Iterable;
use Exception;

/**
 * Class PlayerGroup
 * @package App\Battle\Models
 * @method Player[] getIterator
 */
class PlayerGroup extends Iterable
{
	/**
	 * @var Player[] $array
	 */
	protected $array = [];
	public $battleResult;
	private static $id_count = 0;
	private $id;

	public function __construct($players = [])
	{
		$this->id = ++self::$id_count;
		foreach ($players as $player)
		{
			$this->addPlayer($player);
		}
	}
	public function getId()
	{
		return $this->id;
	}
	public function decrement($idPlayer, $idFleet, $idShipType, $count)
	{
		if (!$this->existPlayer($idPlayer))
		{
			throw new Exception('Player with id : ' . $idPlayer . ' not exist');
		}
		$this->array[$idPlayer]->decrement($idFleet, $idShipType, $count);
		if ($this->array[$idPlayer]->isEmpty())
		{
			unset($this->array[$idPlayer]);
		}
	}

	public function getPlayer($id)
	{
		return isset($this->array[$id]) ? $this->array[$id] : false;
	}

	public function existPlayer($id)
	{
		return isset($this->array[$id]);
	}

	public function addPlayer(Player $player)
	{
		$this->array[$player->getId()] = $player->cloneMe();
	}

	public function createPlayerIfNotExist($id, $fleets, $militaryTech, $shieldTech, $defenceTech)
	{
		if (!$this->existPlayer($id))
		{
			$this->addPlayer(new Player($id, $fleets, $militaryTech, $shieldTech, $defenceTech));
		}
		return $this->getPlayer($id);
	}

	public function isEmpty()
	{
		foreach ($this->array as $id => $player)
		{
			if (!$player->isEmpty())
			{
				return false;
			}
		}
		return true;
	}

	public function __toString()
	{
		ob_start();

		$_st = '';
		/** @noinspection PhpUnusedLocalVariableInspection */
		$_playerGroup = $this;

		require(ROOT_PATH."app/classes/Battle/Views/playerGroup.html");

		return ob_get_clean();
	}

	public function inflictDamage(FireManager $fire)
	{
		$physicShots = [];
		foreach ($this->array as $idPlayer => $player)
		{
			echo "---------** firing to player with ID = $idPlayer **---------- <br>";
			$ps = $player->inflictDamage($fire);
			$physicShots[$idPlayer] = $ps;
		}
		return $physicShots;
	}

	public function cleanShips()
	{
		$shipsCleaners = [];
		foreach ($this->array as $idPlayer => $player)
		{
			echo "---------** cleanShips to player with ID = $idPlayer **---------- <br>";
			$sc = $player->cleanShips();
			$shipsCleaners[] = $sc;
			if ($player->isEmpty())
			{
				unset($this->array[$idPlayer]);
			}
		}
		return $shipsCleaners;
	}

	public function repairShields($round = 0)
	{
		foreach ($this->array as $idPlayer => $player)
		{
			$player->repairShields($round);
		}
	}

	public function getEquivalentFleetContent()
	{
		$merged = new Fleet(-1);
		foreach ($this->array as $idPlayer => $player) // cloning don't have any sense because we don't touch the array,maybe php bug :(
		{
			$merged->mergeFleet($player->getEquivalentFleetContent());
		}
		return $merged;
	}

	public function getTotalCount()
	{
		$amount = 0;
		foreach ($this->array as $idPlayer => $player)
		{
			$amount += $player->getTotalCount();
		}
		return $amount;

	}

	public function cloneMe()
	{
		$players = array_values($this->array);
		$tmp = new PlayerGroup($players);
		$tmp->battleResult = $this->battleResult;
		$tmp->id = $this->id;
		self::$id_count--;
		return $tmp;
	}


}
