<?php

namespace App\Planet;

use App\Entity\Coordinates;
use App\Models\Planet;
use App\Planet\Contracts\PlanetEntityProductionInterface;
use App\Models\User;
use App\Vars;
use Illuminate\Support\Carbon;

class Production
{
	private $planet;
	private $updateTime;

	private $basic;
	private $storage;
	private $production;

	public function __construct(Planet $planet, Carbon $updateTime = null)
	{
		$this->planet = $planet;

		if (!$updateTime) {
			$updateTime = now();
		}

		$this->updateTime = $updateTime;
		$this->calculate();
	}

	private function calculate()
	{
		if (!$this->planet->user instanceof User) {
			return;
		}

		if ($this->updateTime->lessThan($this->planet->last_update)) {
			return;
		}

		$this->updatePlanetResources();

		$this->planet->last_update = $this->updateTime;

		if (!defined('CRON')) {
			$this->planet->last_active = $this->planet->last_update;
		}
	}

	public function reset()
	{
		$this->updateTime = now();
		$this->basic = null;
		$this->storage = null;
		$this->production = null;

		$this->calculate();
	}

	public function update(bool $simulation = false)
	{
		if (!$simulation) {
			$this->planet->update();
		}

		$this->planet->planet_updated = true;
	}

	public function getProductionFactor(): int
	{
		if ($this->planet->energy_max == 0) {
			$factor = 0;
		} elseif ($this->planet->energy_max >= $this->planet->energy_used) {
			$factor = 100;
		} else {
			$factor = round(($this->planet->energy_max / $this->planet->energy_used) * 100, 1);
		}

		return min(max($factor, 0), 100);
	}

	public function getStorageCapacity(): Resources
	{
		if ($this->storage) {
			return $this->storage;
		}

		$resources = [];

		foreach (Vars::getResources() as $res) {
			$resources[$res] = floor((config('settings.baseStorageSize', 0) + floor(50000 * round(pow(1.6, $this->planet->getLevel($res . '_store'))))) * $this->planet->user->bonusValue('storage'));
		}

		$this->storage = new Resources($resources);

		return $this->storage;
	}

	public function getBasicProduction(): Resources
	{
		if ($this->basic) {
			return $this->basic;
		}

		$resources = [];

		foreach (Vars::getResources() as $res) {
			if (!$this->planet->user->isVacation() && !in_array($this->planet->planet_type, [Coordinates::TYPE_MOON, Coordinates::TYPE_MILITARY_BASE])) {
				$resources[$res] = config('settings.' . $res . '_basic_income', 0) * config('settings.resource_multiplier', 1);
			} else {
				$resources[$res] = 0;
			}
		}

		$this->basic = new Resources($resources);

		return $this->basic;
	}

	public function getResourceProduction(): Resources
	{
		if ($this->production) {
			return $this->production;
		}

		$this->planet->energy_used = 0;
		$this->planet->energy_max = 0;

		$resources = new Resources();

		if ($this->planet->user->isVacation()) {
			return $resources;
		}

		if (in_array($this->planet->planet_type, [Coordinates::TYPE_MOON, Coordinates::TYPE_MILITARY_BASE])) {
			return $resources;
		}

		$itemsId = Vars::getItemsByType('prod');

		foreach ($itemsId as $productionId) {
			$entity = $this->planet->getEntity($productionId);

			if (!($entity instanceof PlanetEntityProductionInterface) || $entity->getLevel() <= 0) {
				continue;
			}

			$factor = $entity->factor;

			if ($productionId == 12 && $this->planet->deuterium < 100) {
				$factor = 0;
			}

			$production = $entity->getProduction($factor);

			$resources->add($production);

			if ($productionId < 4) {
				$this->planet->energy_used += abs($production->get(Resources::ENERGY));
			} else {
				$this->planet->energy_max += $production->get(Resources::ENERGY);
			}
		}

		$resources->multiply($this->getProductionFactor() / 100);
		$resources->add($this->getBasicProduction());

		$this->production = $resources;

		return $this->production;
	}

	private function updatePlanetResources()
	{
		$time = $this->updateTime->timestamp - $this->planet->last_update?->timestamp;

		$resourceProduction = $this->getResourceProduction();
		$storageCapacity = $this->getStorageCapacity();
		$storageCapacity->set($storageCapacity::ENERGY, $this->planet->energy_max);

		foreach (Vars::getResources() as $res) {
			if ($this->planet->{$res} <= $storageCapacity->get($res)) {
				$this->planet->{$res} += $time * (($resourceProduction->get($res) / 3600));
			}

			$this->planet->{$res} = max(0, min($this->planet->{$res}, $storageCapacity->get($res)));
		}
	}
}
