<?php

namespace App\Engine\CombatEngine\Models;

use App\Engine\CombatEngine\CombatObject\FireManager;
use App\Engine\CombatEngine\Utils\IterableIterator;
use App\Engine\CombatEngine\Exception;

/**
 * @method Ship[] getIterator()
 * @property ShipType[] $array
 */
class Fleet extends IterableIterator
{
	private $count;
	private $id;
	// added but only used in report templates
	private $weapons_tech = 0;
	private $shields_tech = 0;
	private $armour_tech = 0;
	private $name;

	public function __construct($id, $shipTypes = [], $weapons_tech = null, $shields_tech = null, $armour_tech = null, $name = "")
	{
		$this->id = $id;
		$this->count = 0;
		$this->name = $name;

		if ($this->id != -1) {
			$this->setTech($weapons_tech, $shields_tech, $armour_tech);
		}

		foreach ($shipTypes as $shipType) {
			$this->addShipType($shipType);
		}
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setTech($weapons = null, $shields = null, $armour = null)
	{
		foreach ($this->array as $shipType) {
			$shipType->setWeaponsTech($weapons);
			$shipType->setShieldsTech($shields);
			$shipType->setArmourTech($armour);
		}

		if (is_numeric($weapons)) {
			$this->weapons_tech = intval($weapons);
		}
		if (is_numeric($shields)) {
			$this->shields_tech = intval($shields);
		}
		if (is_numeric($armour)) {
			$this->armour_tech = intval($armour);
		}
	}

	public function addShipType(ShipType $shipType)
	{
		if (isset($this->array[$shipType->getId()])) {
			$this->array[$shipType->getId()]->increment($shipType->getCount());
		} else {
			$shipType = $shipType->cloneMe();//avoid collateral effects

			if ($this->id != -1) {
				if ($this->weapons_tech > 0) {
					$shipType->setWeaponsTech($this->weapons_tech);
				}
				if ($this->shields_tech > 0) {
					$shipType->setShieldsTech($this->shields_tech);
				}
				if ($this->armour_tech > 0) {
					$shipType->setArmourTech($this->armour_tech);
				}
			}

			$this->array[$shipType->getId()] = $shipType;
		}

		$this->count += $shipType->getCount();
	}

	public function decrement($id, $count)
	{
		$this->array[$id]->decrement($count);
		$this->count -= $count;
		if ($this->array[$id]->getCount() <= 0) {
			unset($this->array[$id]);
		}
	}

	public function mergeFleet(Fleet $other)
	{
		foreach ($other->getIterator() as $idShipType => $shipType) {
			$this->addShipType($shipType);
		}
	}

	public function getShipType($id)
	{
		return $this->array[$id];
	}

	public function existShipType($id)
	{
		return isset($this->array[$id]);
	}

	public function getTypeCount($type)
	{
		return $this->array[$type]->getCount();
	}

	public function getTotalCount()
	{
		return $this->count;
	}

	public function inflictDamage(FireManager $fires)
	{
		$physicShots = [];
		//doesn't matter who shot first, but who receive first the damage
		foreach ($fires->getIterator() as $fire) {
			$tmp = [];

			foreach ($this->getOrderedIterator() as $idShipTypeDefender => $shipTypeDefender) {
				$idShipTypeAttacker = $fire->getId();
				\log_comment("---- firing from $idShipTypeAttacker to $idShipTypeDefender ----");
				$xs = $fire->getShotsFiredByAllToDefenderType($shipTypeDefender, true);
				$ps = $shipTypeDefender->inflictDamage($fire->getPower(), $xs->result);
				\log_var('$xs', $xs);
				$tmp[$idShipTypeDefender] = $xs->rest;

				if ($ps != null) {
					$physicShots[$idShipTypeDefender][] = $ps;
				}
			}

			\log_var('$tmp', $tmp);
			// assign the last shot to the more likely shitType
			$m = 0;
			$f = 0;

			foreach ($tmp as $k => $v) {
				if ($v > $m) {
					$m = $v;
					$f = $k;
				}
			}

			if ($f != 0) {
				\log_comment('adding 1 shot');
				$ps = $this->getShipType($f)->inflictDamage($fire->getPower(), 1);
				$physicShots[$f][] = $ps;
			}
		}

		return $physicShots;
	}

	public function getOrderedIterator()
	{
		ksort($this->array);

		return $this->array;
	}

	public function cleanShips()
	{
		$shipsCleaners = [];

		foreach ($this->array as $id => $shipType) {
			\log_comment("---- exploding $id ----");
			$sc = $shipType->cleanShips();
			$this->count -= $sc->getExplodedShips();

			if ($shipType->isEmpty()) {
				unset($this->array[$id]);
			}

			$shipsCleaners[$shipType->getId()] = $sc;
		}
		return $shipsCleaners;
	}

	public function repairShields($round = 0)
	{
		foreach ($this->array as $shipTypeDefender) {
			$shipTypeDefender->repairShields($round);
		}
	}

	public function isEmpty()
	{
		foreach ($this->array as $shipType) {
			if (!$shipType->isEmpty()) {
				return false;
			}
		}

		return true;
	}

	public function getWeaponsTech()
	{
		return $this->weapons_tech;
	}

	public function getShieldsTech()
	{
		return $this->shields_tech;
	}

	public function getArmourTech()
	{
		return $this->armour_tech;
	}

	public function cloneMe(): self
	{
		$types = array_values($this->array);
		$class = get_class($this);

		return new $class($this->id, $types, $this->weapons_tech, $this->shields_tech, $this->armour_tech);
	}
}
