<?php

namespace Xnova\Planet;

use Xnova\Entity\Coordinates;
use Xnova\Planet;
use Xnova\User;
use Xnova\Vars;

class Production
{
	private $planet;

	public function __construct(Planet $planet)
	{
		$this->planet = $planet;
	}

	public function update(int $updateTime = 0, bool $simulation = false)
	{
		$user = $this->planet->getUser();

		if (!$user instanceof User) {
			return false;
		}

		if (!$updateTime) {
			$updateTime = time();
		}

		if ($updateTime < $this->planet->last_update) {
			return false;
		}

		$this->fillMaxStorageValues();
		$this->resourceProductions();

		$productionTime = $updateTime - $this->planet->last_update;

		$this->planet->last_update = $updateTime;

		if (!defined('CRON')) {
			$this->planet->last_active = $this->planet->last_update;
		}

		$this->fillResources($productionTime, $this->getProductionFactor());

		if (!$simulation) {
			$this->planet->update();
		}

		$this->planet->planet_updated = true;

		return true;
	}

	private function getProductionFactor(): int
	{
		if ($this->planet->energy_max == 0) {
			foreach (Vars::getResources() as $res) {
				$this->{$res . '_perhour'} = config('settings.' . $res . '_basic_income', 0);
			}

			$factor = 0;
		} elseif ($this->planet->energy_max >= abs($this->planet->energy_used)) {
			$factor = 100;
		} else {
			$factor = round(($this->planet->energy_max / abs($this->planet->energy_used)) * 100, 1);
		}

		return min(max($factor, 0), 100);
	}

	private function fillMaxStorageValues()
	{
		$user = $this->planet->getUser();

		foreach (Vars::getResources() as $res) {
			$this->{$res . '_max'}  = floor((config('settings.baseStorageSize', 0) + floor(50000 * round(pow(1.6, $this->planet->getLevel($res . '_store'))))) * $user->bonusValue('storage'));
		}
	}

	private function fillResources($time, $factor)
	{
		$user = $this->planet->getUser();

		foreach (Vars::getResources() as $res) {
			$this->planet->{$res . '_production'} = 0;

			if ($this->planet->{$res} <= $this->planet->{$res . '_max'}) {
				$this->planet->{$res . '_production'} = ($time * ($this->planet->{$res . '_perhour'} / 3600)) * (0.01 * $factor);

				if (!$user->isVacation()) {
					$this->planet->{$res . '_base'} = ($time * (config('settings.' . $res . '_basic_income', 0) / 3600)) * config('settings.resource_multiplier', 1);
				} else {
					$this->planet->{$res . '_base'} = 0;
				}

				$this->planet->{$res . '_production'} = $this->planet->{$res . '_production'} + $this->planet->{$res . '_base'};

				if (($this->planet->{$res} + $this->planet->{$res . '_production'}) > $this->{$res . '_max'}) {
					$this->planet->{$res . '_production'} = $this->planet->{$res . '_max'} - $this->planet->{$res};
				}
			}

			$this->planet->{$res . '_perhour'} = round(floatval($this->planet->{$res . '_perhour'}) * (0.01 * $factor));
			$this->planet->{$res} += $this->planet->{$res . '_production'};

			if ($this->planet->{$res} < 0) {
				$this->planet->{$res} = 0;
			}
		}
	}

	public function resourceProductions()
	{
		$this->planet->energy_used = 0;
		$this->planet->energy_max = 0;

		foreach (Vars::getResources() as $res) {
			$this->{$res . '_perhour'} = 0;
		}

		$user = $this->planet->getUser();

		if ($user->isVacation()) {
			return;
		}

		if (in_array($this->planet->planet_type, [Coordinates::TYPE_MOON, Coordinates::TYPE_MILITARY_BASE])) {
			foreach (Vars::getResources() as $res) {
				config(['settings.' . $res . '_basic_income' => 0]);
			}

			return;
		}

		$context = new Planet\Entity\Context($user, $this->planet);

		$itemsId = Vars::getItemsByType('prod');

		foreach ($itemsId as $productionId) {
			$build = $this->planet->getEntity($productionId);

			if (!$build || $build->amount <= 0) {
				continue;
			}

			if (!Vars::getBuildProduction($productionId)) {
				continue;
			}

			$factor = $build->factor;

			if ($productionId == 12 && $this->planet->deuterium < 100) {
				$factor = 0;
			}

			$result = $build->getProduction($context, $factor);

			foreach (Vars::getResources() as $res) {
				$this->{$res . '_perhour'} += $result->get($res);
			}

			if ($productionId < 4) {
				$this->planet->energy_used += $result->get(Resources::ENERGY);
			} else {
				$this->planet->energy_max += $result->get(Resources::ENERGY);
			}
		}
	}
}
