<?php

namespace App\Engine\Ai;

use App\Engine\Enums\ItemType;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\BuildingObject;
use App\Facades\Vars;
use App\Models\Planet;

class StrategyPlanner
{
	protected array $weights;

	public function __construct(protected Planet $planet, protected StrategyType $strategy)
	{
		$this->initWeights();
	}

	private function initWeights(): void
	{
		$this->weights = [
			'economy' => [
				'build' => ['metal_mine' => 95, 'crystal_mine' => 95, 'deuterium_mine' => 90, 'solar_plant' => 100, 'fusion_plant' => 60, 'metal_store' => 85, 'crystal_store' => 85, 'deuterium_store' => 70, 'robot_factory' => 80, 'hangar' => 70, 'laboratory' => 85, 'terraformer' => 50, 'moonbase' => 40, 'missile_facility' => 30, 'nano_factory' => 60],
				'research' => ['energy_tech' => 80, 'laser_tech' => 60, 'ionic_tech' => 50, 'hyperspace_tech' => 40, 'buster_tech' => 30, 'combustionic_tech' => 50, 'impulse_motor_tech' => 40, 'hyperspace_motor_tech' => 30, 'spy_tech' => 90, 'computer_tech' => 85, 'colonization_tech' => 75, 'intergalactic_tech' => 70, 'graviton_tech' => 20, 'military_tech' => 40, 'shield_tech' => 40, 'defence_tech' => 40],
				'fleet' => ['light_hunter' => 40, 'heavy_hunter' => 30, 'crusher' => 20, 'battle_ship' => 15, 'battle_cruiser' => 15, 'bomber_ship' => 10, 'destructor' => 10, 'dearth_star' => 5, 'recycler' => 95, 'spy_sonde' => 90, 'small_ship_cargo' => 95, 'big_ship_cargo' => 90, 'colonizer' => 85, 'solar_satelit' => 90],
				'defense' => ['misil_launcher' => 60, 'small_laser' => 55, 'big_laser' => 40, 'gauss_canyon' => 30, 'ionic_canyon' => 25, 'buster_canyon' => 20, 'small_protection_shield' => 35, 'big_protection_shield' => 25, 'interplanetary_misil' => 50, 'interceptor_misil' => 20],
			],
			'military' => [
				'build' => ['metal_mine' => 60, 'crystal_mine' => 65, 'deuterium_mine' => 55, 'solar_plant' => 70, 'fusion_plant' => 40, 'metal_store' => 50, 'crystal_store' => 55, 'deuterium_store' => 45, 'robot_factory' => 95, 'hangar' => 100, 'laboratory' => 90, 'terraformer' => 30, 'moonbase' => 50, 'missile_facility' => 40, 'nano_factory' => 85],
				'research' => ['energy_tech' => 60, 'laser_tech' => 50, 'ionic_tech' => 55, 'hyperspace_tech' => 65, 'buster_tech' => 50, 'combustionic_tech' => 85, 'impulse_motor_tech' => 90, 'hyperspace_motor_tech' => 80, 'spy_tech' => 70, 'computer_tech' => 75, 'colonization_tech' => 60, 'intergalactic_tech' => 65, 'graviton_tech' => 30, 'military_tech' => 95, 'shield_tech' => 90, 'defence_tech' => 95],
				'fleet' => ['light_hunter' => 50, 'heavy_hunter' => 60, 'crusher' => 75, 'battle_ship' => 80, 'battle_cruiser' => 85, 'bomber_ship' => 90, 'destructor' => 95, 'dearth_star' => 85, 'recycler' => 60, 'spy_sonde' => 70, 'small_ship_cargo' => 55, 'big_ship_cargo' => 60, 'colonizer' => 30, 'solar_satelit' => 40],
				'defense' => ['misil_launcher' => 70, 'small_laser' => 75, 'big_laser' => 80, 'gauss_canyon' => 85, 'ionic_canyon' => 75, 'buster_canyon' => 70, 'small_protection_shield' => 65, 'big_protection_shield' => 60, 'interplanetary_misil' => 55, 'interceptor_misil' => 65],
			],
			'balanced' => [
				'build' => ['metal_mine' => 80, 'crystal_mine' => 80, 'deuterium_mine' => 75, 'solar_plant' => 85, 'fusion_plant' => 65, 'metal_store' => 70, 'crystal_store' => 70, 'deuterium_store' => 65, 'robot_factory' => 85, 'hangar' => 85, 'laboratory' => 85, 'terraformer' => 60, 'moonbase' => 55, 'missile_facility' => 50, 'nano_factory' => 75],
				'research' => ['energy_tech' => 80, 'laser_tech' => 70, 'ionic_tech' => 65, 'hyperspace_tech' => 60, 'buster_tech' => 55, 'combustionic_tech' => 75, 'impulse_motor_tech' => 70, 'hyperspace_motor_tech' => 65, 'spy_tech' => 75, 'computer_tech' => 80, 'colonization_tech' => 70, 'intergalactic_tech' => 70, 'graviton_tech' => 25, 'military_tech' => 75, 'shield_tech' => 75, 'defence_tech' => 75],
				'fleet' => ['light_hunter' => 70, 'heavy_hunter' => 65, 'crusher' => 75, 'battle_ship' => 70, 'battle_cruiser' => 70, 'bomber_ship' => 65, 'destructor' => 75, 'dearth_star' => 50, 'recycler' => 80, 'spy_sonde' => 80, 'small_ship_cargo' => 80, 'big_ship_cargo' => 75, 'colonizer' => 60, 'solar_satelit' => 70],
				'defense' => ['misil_launcher' => 70, 'small_laser' => 70, 'big_laser' => 65, 'gauss_canyon' => 60, 'ionic_canyon' => 55, 'buster_canyon' => 50, 'small_protection_shield' => 60, 'big_protection_shield' => 50, 'interplanetary_misil' => 65, 'interceptor_misil' => 45],
			],
		];
	}

	public function getWeightFor(BaseObject $object): int
	{
		return $this->weights[$this->strategy->value][$object->getType()->value][$object->getCode()] ?? 0;
	}

	public function getRecommendations(ItemType $type, int $limit = 10): array
	{
		$result = [];

		$objects = Vars::getObjectsByType($type);

		foreach ($objects as $object) {
			if ($object instanceof BuildingObject && !$object->hasAllowedBuild($this->planet->planet_type)) {
				continue;
			}

			$entity = $this->planet->getEntityUnit($object);
			$weight = $this->getWeightFor($object);
			$score  = $this->calculatePriorityScore($weight, $entity->getTime());

			$result[] = [
				'id' 		=> $object->getId(),
				'code' 		=> $object->getCode(),
				'level' 	=> $entity->getLevel() + 1,
				'score' 	=> $score,
			];
		}

		usort($result, static fn($a, $b) => $b['score'] <=> $a['score']);

		return array_slice($result, 0, $limit);
	}

	private function calculatePriorityScore(int $weight, float $timeMinutes): float
	{
		return $weight / (1 + log10(max(1.0, $timeMinutes)));
	}
}
