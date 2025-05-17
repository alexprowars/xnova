<?php

namespace App\Engine;

use App\Engine\Contracts\EntityProductionInterface;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\Resources as ResourcesEnum;
use App\Models\Planet;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class Production
{
	protected $basic;
	protected $storage;
	protected $production;

	public function __construct(protected Planet $planet, protected null|Carbon|CarbonImmutable $updateTime = null)
	{
		if (!$this->updateTime) {
			$this->updateTime = now();
		}

		$this->calculate();
	}

	protected function calculate()
	{
		if (!$this->planet->user instanceof User) {
			return;
		}

		if ($this->updateTime->lessThan($this->planet->last_update)) {
			return;
		}

		$this->planet->last_update ??= now();
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
		if ($this->planet->energy == 0) {
			$factor = 0;
		} elseif ($this->planet->energy >= $this->planet->energy_used) {
			$factor = 100;
		} else {
			$factor = round(($this->planet->energy / $this->planet->energy_used) * 100, 1);
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
			$resources[$res] = floor((config('game.baseStorageSize', 0) + floor(50000 * round(1.6 ** $this->planet->getLevel($res . '_store')))) * $this->planet->user->bonus('storage'));
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
			if (!$this->planet->user->isVacation() && !in_array($this->planet->planet_type, [PlanetType::MOON, PlanetType::MILITARY_BASE])) {
				$resources[$res] = config('game.' . $res . '_basic_income', 0) * Game::getSpeed('mine');
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
		$this->planet->energy = 0;

		$resources = new Resources();

		if ($this->planet->user->isVacation()) {
			return $resources;
		}

		if (in_array($this->planet->planet_type, [PlanetType::MOON, PlanetType::MILITARY_BASE])) {
			return $resources;
		}

		$itemsId = Vars::getItemsByType(ItemType::PRODUCTION);

		foreach ($itemsId as $productionId) {
			$entity = $this->planet->getEntity($productionId)->unit();

			if (!$entity || $entity->getLevel() <= 0) {
				continue;
			}

			$factor = null;

			if ($productionId == 12 && $this->planet->deuterium < 100) {
				$factor = 0;
			}

			$production = $entity->getProduction($factor);

			$resources->add($production);

			if ($productionId < 4) {
				$this->planet->energy_used += abs($production->get(ResourcesEnum::ENERGY));
			} else {
				$this->planet->energy += $production->get(ResourcesEnum::ENERGY);
			}
		}

		$resources->multiply($this->getProductionFactor() / 100, [ResourcesEnum::ENERGY]);
		$resources->add($this->getBasicProduction());

		$this->production = $resources;

		return $this->production;
	}

	protected function updatePlanetResources()
	{
		$time = $this->planet->last_update->diffInSeconds($this->updateTime);

		if ($time < 0) {
			return;
		}

		$resourceProduction = $this->getResourceProduction();
		$storageCapacity = $this->getStorageCapacity();
		$storageCapacity->set(ResourcesEnum::ENERGY, $this->planet->energy);

		foreach (Vars::getResources() as $res) {
			if ($this->planet->{$res} >= $storageCapacity->get($res)) {
				continue;
			}

			$this->planet->{$res} += $time * (($resourceProduction->get($res) / 3600));
			$this->planet->{$res} = max(0, min($this->planet->{$res}, $storageCapacity->get($res)));
		}
	}
}
