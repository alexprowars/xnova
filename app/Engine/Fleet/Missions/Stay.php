<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Format;
use App\Models\Planet;
use App\Notifications\MessageNotification;

class Stay extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $targetPlanet && ($targetPlanet->user_id == $planet->user_id || $targetPlanet->user->isAdmin());
	}

	public function targetEvent()
	{
		$targetPlanet = Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

		if (!$targetPlanet || $targetPlanet->user_id != $this->fleet->target_user_id) {
			$this->return();
		} else {
			$this->restoreFleetToPlanet(false);
			$this->killFleet();

			$TargetAddedGoods = '';

			foreach ($this->fleet->entities as $entity) {
				$TargetAddedGoods .= ', ' . __('main.tech.' . $entity->id) . ': ' . $entity->count;
			}

			$targetMessage = __('fleet_engine.sys_stat_mess', [
				'target' => $this->fleet->getTargetAdressLink(),
				'm' => Format::number($this->fleet->resource_metal),
				'mt' => __('main.metal'),
				'c' => Format::number($this->fleet->resource_crystal),
				'ct' => __('main.crystal'),
				'd' => Format::number($this->fleet->resource_deuterium),
				'dt' => __('main.deuterium')
			]);

			if ($TargetAddedGoods != '') {
				$targetMessage .= '<br>' . trim(substr($TargetAddedGoods, 1));
			}

			$this->fleet->target->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_qg'), $targetMessage));
		}
	}

	public function returnEvent()
	{
		$targetPlanet = Planet::findByCoordinates($this->fleet->getOriginCoordinates());

		if (!$targetPlanet || $targetPlanet->user_id != $this->fleet->user_id) {
			$this->killFleet();
		} else {
			$this->restoreFleetToPlanet();
			$this->killFleet();

			$targetAddedGoods = __('fleet_engine.sys_stay_mess_goods', [
				'mt' => __('main.metal', locale: $this->fleet->user->locale),
				'm' => Format::number($this->fleet->resource_metal),
				'ct' => __('main.crystal', locale: $this->fleet->user->locale),
				'c' => Format::number($this->fleet->resource_crystal),
				'dt' => __('main.deuterium', locale: $this->fleet->user->locale),
				'd' => Format::number($this->fleet->resource_deuterium),
			], $this->fleet->user->locale);

			foreach ($this->fleet->entities as $entity) {
				$targetAddedGoods .= ', ' . __('main.tech.' . $entity->id, locale: $this->fleet->user->locale) . ': ' . $entity->count;
			}

			$TargetMessage = __('fleet_engine.sys_stay_mess_back', locale: $this->fleet->user->locale) . $this->fleet->getTargetAdressLink() . __('fleet_engine.sys_stay_mess_bend', locale: $this->fleet->user->locale) . '<br>' . $targetAddedGoods;

			$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_qg'), $TargetMessage));
		}
	}
}
