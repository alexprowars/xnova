<?php

namespace App\Engine\Traits\User;

trait HasBonuses
{
	protected $bonusData;

	protected function fillBobusData()
	{
		$bonusArrays = [
			'storage', 'metal', 'crystal', 'deuterium', 'energy', 'solar',
			'res_fleet', 'res_defence', 'res_research', 'res_building', 'res_levelup',
			'time_fleet', 'time_defence', 'time_research', 'time_building',
			'fleet_fuel', 'fleet_speed', 'queue',
		];

		$this->bonusData = [];

		// Значения по умолчанию
		foreach ($bonusArrays as $name) {
			$this->bonusData[$name] = 1;
		}

		$this->bonusData['queue'] = 0;

		// Расчет бонусов от офицеров
		if ($this->rpg_geologue?->isFuture()) {
			$this->bonusData['metal'] 			+= 0.25;
			$this->bonusData['crystal'] 		+= 0.25;
			$this->bonusData['deuterium'] 		+= 0.25;
			$this->bonusData['storage'] 		+= 0.25;
		}
		if ($this->rpg_ingenieur?->isFuture()) {
			$this->bonusData['energy'] 			+= 0.15;
			$this->bonusData['solar'] 			+= 0.15;
			$this->bonusData['res_defence'] 	-= 0.1;
		}
		if ($this->rpg_admiral?->isFuture()) {
			$this->bonusData['res_fleet'] 		-= 0.1;
			$this->bonusData['fleet_speed'] 	+= 0.25;
		}
		if ($this->rpg_constructeur?->isFuture()) {
			$this->bonusData['time_fleet'] 		-= 0.25;
			$this->bonusData['time_defence'] 	-= 0.25;
			$this->bonusData['time_building'] 	-= 0.25;
			$this->bonusData['queue'] 			+= 2;
		}
		if ($this->rpg_technocrate?->isFuture()) {
			$this->bonusData['time_research'] 	-= 0.25;
		}
		if ($this->rpg_meta?->isFuture()) {
			$this->bonusData['fleet_fuel'] 		-= 0.2;
		}

		// Расчет бонусов от рас
		if ($this->race == 1) {
			$this->bonusData['metal'] 			+= 0.15;
			$this->bonusData['solar'] 			+= 0.15;
			$this->bonusData['res_levelup'] 	-= 0.1;
			$this->bonusData['time_fleet'] 		-= 0.1;
		} elseif ($this->race == 2) {
			$this->bonusData['deuterium'] 		+= 0.15;
			$this->bonusData['solar'] 			+= 0.05;
			$this->bonusData['storage'] 		+= 0.2;
			$this->bonusData['res_fleet'] 		-= 0.1;
		} elseif ($this->race == 3) {
			$this->bonusData['metal'] 			+= 0.05;
			$this->bonusData['crystal'] 		+= 0.05;
			$this->bonusData['deuterium'] 		+= 0.05;
			$this->bonusData['res_defence'] 	-= 0.05;
			$this->bonusData['res_building'] 	-= 0.05;
			$this->bonusData['time_building'] 	-= 0.1;
		} elseif ($this->race == 4) {
			$this->bonusData['crystal'] 		+= 0.15;
			$this->bonusData['energy'] 			+= 0.05;
			$this->bonusData['res_research'] 	-= 0.1;
			$this->bonusData['fleet_speed'] 	+= 0.1;
		}

		return true;
	}

	public function bonus($key, $default = null): float
	{
		if (!$this->bonusData) {
			$this->fillBobusData();
		}

		return $this->bonusData[$key] ?? ($default ?? 1.0);
	}

	public function setBonus($key, $value)
	{
		if (!$this->bonusData) {
			$this->fillBobusData();
		}

		$this->bonusData[$key] = $value;
	}
}
