<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Facades\Vars;
use App\Format;
use App\Models;
use App\Models\User;

class Fleet extends Building
{
	public static function setShipsEngine(User $user)
	{
		$storage = Vars::getStorage();

		foreach (Vars::getItemsByType(ItemType::FLEET) as $ship) {
			if (isset($storage['CombatCaps'][$ship]['engine_up']) && $user->getTechLevel($storage['CombatCaps'][$ship]['engine_up']['tech']) >= $storage['CombatCaps'][$ship]['engine_up']['lvl']) {
				$tmp = $storage['CombatCaps'][$ship];
				$tmp['type_engine']++;
				$tmp['speed'] = $tmp['engine_up']['speed'];
				unset($tmp['engine_up']);

				Vars::updateStorage('CombatCaps.' . $ship, $tmp);
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

	public static function createFleetPopupedMissionLink(Models\Fleet $FleetRow, string $Texte, $FleetType)
	{
		$FleetTotalC = $FleetRow->resource_metal + $FleetRow->resource_crystal + $FleetRow->resource_deuterium;

		if ($FleetTotalC != 0) {
			$FRessource = '<table width=200>';
			$FRessource .= '<tr><td width=50% align=left><span style="color: white">' . __('main.metal') . '<span></td><td width=50% align=right><span style="color: white">' . Format::number($FleetRow->resource_metal) . '<span></td></tr>';
			$FRessource .= '<tr><td width=50% align=left><span style="color: white">' . __('main.crystal') . '<span></td><td width=50% align=right><span style="color: white">' . Format::number($FleetRow->resource_crystal) . '<span></td></tr>';
			$FRessource .= '<tr><td width=50% align=left><span style="color: white">' . __('main.deuterium') . '<span></td><td width=50% align=right><span style="color: white">' . Format::number($FleetRow->resource_deuterium) . '<span></td></tr>';
			$FRessource .= '</table>';
		} else {
			$FRessource = '';
		}

		if ($FRessource <> '') {
			$MissionPopup = '<a href="javascript:;" data-content="' . $FRessource . '" class="tooltip ' . $FleetType . '">' . $Texte . '</a>';
		} else {
			$MissionPopup = $Texte;
		}

		return $MissionPopup;
	}
}
