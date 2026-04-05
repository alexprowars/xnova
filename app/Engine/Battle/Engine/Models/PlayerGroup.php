<?php

namespace App\Engine\Battle\Engine\Models;

use App\Engine\Battle\Engine\BattleResult;
use App\Engine\Battle\Engine\Exception;
use App\Engine\Battle\Engine\Objects\FireManager;

class PlayerGroup
{
	public ?BattleResult $battleResult = null;
	private static $id_count = 0;
	private $id;
	/** @var Player[] */
	protected array $array = [];

	public function __construct($players = [])
	{
		$this->id = ++self::$id_count;

		foreach ($players as $player) {
			$this->addPlayer($player);
		}
	}
	public function getId()
	{
		return $this->id;
	}
	public function decrement($idPlayer, $idFleet, $idShipType, $count)
	{
		if (!$this->existPlayer($idPlayer)) {
			throw new Exception('Player with id : ' . $idPlayer . ' not exist');
		}

		$this->array[$idPlayer]->decrement($idFleet, $idShipType, $count);

		if ($this->array[$idPlayer]->isEmpty()) {
			unset($this->array[$idPlayer]);
		}
	}

	public function getPlayer(int $id): ?Player
	{
		return $this->array[$id] ?? null;
	}

	public function existPlayer(int $id): bool
	{
		return isset($this->array[$id]);
	}

	public function addPlayer(Player $player)
	{
		$this->array[$player->getId()] = $player->cloneMe();
	}

	public function createPlayerIfNotExist($id, $fleets)
	{
		if (!$this->existPlayer($id)) {
			$this->addPlayer(new Player($id, $fleets));
		}

		return $this->getPlayer($id);
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers(): array
	{
		return $this->array;
	}

	public function isEmpty()
	{
		foreach ($this->array as $player) {
			if (!$player->isEmpty()) {
				return false;
			}
		}
		return true;
	}

	public function inflictDamage(FireManager $fire)
	{
		$physicShots = [];
		foreach ($this->array as $idPlayer => $player) {
			echo "---------** firing to player with ID = $idPlayer **---------- <br>";
			$ps = $player->inflictDamage($fire);
			$physicShots[$idPlayer] = $ps;
		}
		return $physicShots;
	}

	public function cleanShips()
	{
		$shipsCleaners = [];
		foreach ($this->array as $idPlayer => $player) {
			echo "---------** cleanShips to player with ID = $idPlayer **---------- <br>";
			$sc = $player->cleanShips();
			$shipsCleaners[] = $sc;
			if ($player->isEmpty()) {
				unset($this->array[$idPlayer]);
			}
		}
		return $shipsCleaners;
	}

	public function repairShields($round = 0)
	{
		foreach ($this->array as $idPlayer => $player) {
			$player->repairShields($round);
		}
	}

	public function getEquivalentFleetContent()
	{
		$merged = new Fleet(-1);
		foreach ($this->array as $idPlayer => $player) { // cloning don't have any sense because we don't touch the array,maybe php bug :(
			$merged->mergeFleet($player->getEquivalentFleetContent());
		}
		return $merged;
	}

	public function getTotalCount()
	{
		$amount = 0;
		foreach ($this->array as $idPlayer => $player) {
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
