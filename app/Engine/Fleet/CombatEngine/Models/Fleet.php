<?php

namespace App\Engine\Fleet\CombatEngine\Models;

use App\Engine\Fleet\CombatEngine\CombatObject\FireManager;
use App\Engine\Fleet\CombatEngine\Utils\IterableIterator;

/**
 * @method Ship[] getIterator()
 * @property ShipType[] $array
 */
class Fleet extends IterableIterator
{
	private int $id;
	private int $count = 0;
	private string $name;

	public function __construct(int $id, array $shipTypes = [], string $name = '')
	{
		$this->id = $id;
		$this->name = $name;

		foreach ($shipTypes as $shipType) {
			$this->addShipType($shipType);
		}
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName($name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function addShipType(ShipType $shipType)
	{
		if (isset($this->array[$shipType->getId()])) {
			$this->array[$shipType->getId()]->increment($shipType->getCount());
		} else {
			$this->array[$shipType->getId()] = $shipType->cloneMe();
		}

		$this->count += $shipType->getCount();
	}

	public function decrement(int $id, int $count): self
	{
		$this->array[$id]->decrement($count);
		$this->count -= $count;

		if ($this->array[$id]->getCount() <= 0) {
			unset($this->array[$id]);
		}

		return $this;
	}

	public function mergeFleet(Fleet $other): self
	{
		foreach ($other->getIterator() as $shipType) {
			$this->addShipType($shipType);
		}

		return $this;
	}

	public function getShipType($id): ShipType
	{
		return $this->array[$id];
	}

	public function existShipType($id): bool
	{
		return isset($this->array[$id]);
	}

	public function getTypeCount($type): int
	{
		return $this->array[$type]->getCount();
	}

	public function getTotalCount(): int
	{
		return $this->count;
	}

	public function inflictDamage(FireManager $fires): array
	{
		$physicShots = [];
		//doesn't matter who shot first, but who receive first the damage
		foreach ($fires->getIterator() as $fire) {
			$tmp = [];

			foreach ($this->getOrderedIterator() as $idShipTypeDefender => $shipTypeDefender) {
				$idShipTypeAttacker = $fire->getId();
				log_comment("---- firing from $idShipTypeAttacker to $idShipTypeDefender ----");
				$xs = $fire->getShotsFiredByAllToDefenderType($shipTypeDefender, true);
				$ps = $shipTypeDefender->inflictDamage($fire->getPower(), $xs->result);
				log_var('$xs', $xs);
				$tmp[$idShipTypeDefender] = $xs->rest;

				if ($ps != null) {
					$physicShots[$idShipTypeDefender][] = $ps;
				}
			}

			log_var('$tmp', $tmp);
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
				log_comment('adding 1 shot');
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

	public function cleanShips(): array
	{
		$shipsCleaners = [];

		foreach ($this->array as $id => $shipType) {
			log_comment("---- exploding $id ----");
			$sc = $shipType->cleanShips();
			$this->count -= $sc->getExplodedShips();

			if ($shipType->isEmpty()) {
				unset($this->array[$id]);
			}

			$shipsCleaners[$shipType->getId()] = $sc;
		}
		return $shipsCleaners;
	}

	public function repairShields($round = 0): self
	{
		foreach ($this->array as $shipTypeDefender) {
			$shipTypeDefender->repairShields($round);
		}

		return $this;
	}

	public function isEmpty(): bool
	{
		foreach ($this->array as $shipType) {
			if (!$shipType->isEmpty()) {
				return false;
			}
		}

		return true;
	}

	public function cloneMe(): self
	{
		$class = get_class($this);

		return new $class($this->id, array_values($this->array));
	}
}
