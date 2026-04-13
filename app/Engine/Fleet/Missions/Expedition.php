<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Actions\Fleet\FinishExpeditionAction;
use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Messages\Types\MissionExpeditionReturnMessage;
use App\Models\Planet;
use App\Notifications\SystemMessage;

class Expedition extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		if ($target->getPlanet() != 16) {
			return false;
		}

		if ($planet->user->getTechLevel('expedition') <= 0) {
			return false;
		}

		return !(count($units) == 1 && !empty($units[210]));
	}

	public function targetEvent(): void
	{
		$this->stayFleet();
	}

	public function endStayEvent(): void
	{
		new FinishExpeditionAction($this->fleet)->handle();
	}

	public function returnEvent(): void
	{
		$message = new MissionExpeditionReturnMessage([
			'metal' => $this->fleet->resource_metal,
			'crystal' => $this->fleet->resource_crystal,
			'deuterium' => $this->fleet->resource_deuterium,
		]);

		$this->fleet->user->notify(
			new SystemMessage(MessageType::Expedition, $message)
		);

		parent::returnEvent();
	}
}
