<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Objects\ShipObject;
use App\Facades\Vars;
use App\Format;
use App\Models;
use App\Models\User;

class Fleet extends Building
{
	public static function setShipsEngine(User $user): void
	{
		$objects = Vars::getObjectsByType(ItemType::FLEET);

		foreach ($objects as $ship) {
			if ($ship instanceof ShipObject && ($upgrade = $ship->getEngineUpgrade()) && $user->getTechLevel($upgrade['tech']) >= $upgrade['lvl']) {
				$ship->upgradeEngine();
			}
		}
	}

	public static function createFleetPopupedFleetLink(Models\Fleet $fleetRow, ?User $user = null): array
	{
		$result = [];

		if ($user && $fleetRow->user_id != $user->id && $user->getTechLevel('spy') < 2) {
			return [];
		} elseif ($user && $fleetRow->user_id != $user->id && $user->getTechLevel('spy') < 4) {
			$result['total'] = $fleetRow->entities->getTotal();
		} elseif ($user && $fleetRow->user_id != $user->id && $user->getTechLevel('spy') < 8) {
			$result['total'] = $fleetRow->entities->getTotal();

			foreach ($fleetRow->entities as $entity) {
				$result[Vars::getName($entity->id)] = null;
			}
		} else {
			foreach ($fleetRow->entities as $entity) {
				$result[Vars::getName($entity->id)] = $entity->count;
			}
		}

		return $result;
	}

	public static function createFleetPopupedMissionLink(Models\Fleet $fleet, string $text, string $fleetType): string
	{
		$totalCount = $fleet->resource_metal + $fleet->resource_crystal + $fleet->resource_deuterium;

		if ($totalCount != 0) {
			$ressource = '<table width=200>';
			$ressource .= '<tr><td width=50% align=left><span style="color: white">' . __('main.metal') . '<span></td><td width=50% align=right><span style="color: white">' . Format::number($fleet->resource_metal) . '<span></td></tr>';
			$ressource .= '<tr><td width=50% align=left><span style="color: white">' . __('main.crystal') . '<span></td><td width=50% align=right><span style="color: white">' . Format::number($fleet->resource_crystal) . '<span></td></tr>';
			$ressource .= '<tr><td width=50% align=left><span style="color: white">' . __('main.deuterium') . '<span></td><td width=50% align=right><span style="color: white">' . Format::number($fleet->resource_deuterium) . '<span></td></tr>';
			$ressource .= '</table>';
		} else {
			$ressource = '';
		}

		if ($ressource <> '') {
			return '<a href="javascript:;" data-content="' . $ressource . '" class="tooltip ' . $fleetType . '">' . $text . '</a>';
		} else {
			return $text;
		}
	}
}
