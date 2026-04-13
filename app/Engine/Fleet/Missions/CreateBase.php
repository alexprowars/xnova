<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Messages\AbstractMessage;
use App\Engine\Messages\Types\MissionCreateBaseMaxReachedMessage;
use App\Engine\Messages\Types\MissionCreateBaseErrorMessage;
use App\Engine\Messages\Types\MissionCreateBaseExistMessage;
use App\Engine\Messages\Types\MissionCreateBaseMessage;
use App\Facades\Galaxy;
use App\Models;
use App\Models\Planet;
use App\Notifications\SystemMessage;

class CreateBase extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return !empty($units[216]) && !$targetPlanet && $target->getType() == PlanetType::PLANET;
	}

	public function targetEvent(): void
	{
		// Определяем максимальное количество баз
		$maxBases = $this->fleet->user->getTechLevel('fleet_base');

		// Получение общего количества построенных баз
		$iPlanetCount = Models\Planet::query()
			->whereBelongsTo($this->fleet->user)
			->where('planet_type', PlanetType::MILITARY_BASE)
			->count();

		// Если в галактике пусто (планета не заселена)
		if (Galaxy::isPositionFree($this->fleet->getDestinationCoordinates())) {
			// Если лимит баз исчерпан
			if ($iPlanetCount >= $maxBases) {
				$this->return();

				$this->sendNotify(new MissionCreateBaseMaxReachedMessage([
					'target' => $this->fleet->getDestinationCoordinates()->toArray(),
					'max' => $maxBases,
				]));
			} else {
				// Создание планеты-базы
				$NewOwnerPlanet = Galaxy::createPlanet(
					$this->fleet->getDestinationCoordinates(),
					$this->fleet->user,
					__('fleet_engine.sys_base_defaultname'),
				);

				// Если планета-база создана
				if ($NewOwnerPlanet) {
					foreach ($this->fleet->entities as $entity) {
						if ($entity->id == 216 && $entity->count > 0) {
							$entity->count--;
						}
					}

					$this->fleet->end_type = PlanetType::MILITARY_BASE;

					$this->restoreFleetToPlanet(false);
					$this->killFleet();

					$this->sendNotify(new MissionCreateBaseMessage([
						'target' => $this->fleet->getDestinationCoordinates()->toArray(),
					]));
				} else {
					$this->return();

					$this->sendNotify(new MissionCreateBaseErrorMessage([
						'target' => $this->fleet->getDestinationCoordinates()->toArray(),
					]));
				}
			}
		} else {
			$this->return();

			$this->sendNotify(new MissionCreateBaseExistMessage([
				'target' => $this->fleet->getDestinationCoordinates()->toArray(),
			]));
		}
	}

	protected function sendNotify(AbstractMessage $message)
	{
		$this->fleet->user->notify(new SystemMessage(MessageType::Fleet, $message));
	}
}
