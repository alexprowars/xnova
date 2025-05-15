<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Fleet\Mission;
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

	public static function createFleetPopupedFleetLink(Models\Fleet $FleetRow, $Texte, $FleetType, ?User $user)
	{
		$FleetRec = $FleetRow->getShips();

		$FleetPopup = '<table width="200">';
		$r = 'javascript:;';

		$Total = 0;

		foreach ($FleetRec as $fleet) {
			$Total += $fleet['count'];
		}

		if ($user && $FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 2) {
			$FleetPopup .= '<tr><td width=100% align=center><font color=white>Нет информации<font></td></tr>';
		} elseif ($user && $FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 4) {
			$FleetPopup .= '<tr><td width=50% align=left><font color=white>Численность:<font></td><td width=50% align=right><font color=white>' . Format::number($Total) . "<font></td></tr>";
		} elseif ($user && $FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 8) {
			foreach ($FleetRec as $id => $fleet) {
				$FleetPopup .= '<tr><td width=100% align=center colspan=2><font color=white>' . __('main.tech.' . $id) . "<font></td></tr>";
			}

			$FleetPopup .= '<tr><td width=50% align=left><font color=white>Численность:<font></td><td width=50% align=right><font color=white>' . Format::number($Total) . "<font></td></tr>";
		} else {
			if ($FleetRow->target_user_id == $user?->id && $FleetRow->mission == Mission::Attack) {
				$r = '/sim/';
			}

			foreach ($FleetRec as $id => $fleet) {
				$FleetPopup .= '<tr><td width=75% align=left><font color=white>' . __('main.tech.' . $id) . ":<font></td><td width=25% align=right><font color=white>" . Format::number($fleet['count']) . "<font></td></tr>";

				if ($r != 'javascript:;') {
					$r .= $id . ',' . $fleet['count'] . ';';
				}
			}
		}

		$FleetPopup .= '</table>';

		return '<a href="' . $r . '" class="tooltip ' . $FleetType . '" data-content="' . $FleetPopup . '">' . $Texte . '</a>';
	}

	public static function createFleetPopupedMissionLink(Models\Fleet $FleetRow, string $Texte, $FleetType)
	{
		$FleetTotalC = $FleetRow->resource_metal + $FleetRow->resource_crystal + $FleetRow->resource_deuterium;

		if ($FleetTotalC != 0) {
			$FRessource = '<table width=200>';
			$FRessource .= '<tr><td width=50% align=left><font color=white>' . __('main.metal') . '<font></td><td width=50% align=right><font color=white>' . Format::number($FleetRow->resource_metal) . '<font></td></tr>';
			$FRessource .= '<tr><td width=50% align=left><font color=white>' . __('main.crystal') . '<font></td><td width=50% align=right><font color=white>' . Format::number($FleetRow->resource_crystal) . '<font></td></tr>';
			$FRessource .= '<tr><td width=50% align=left><font color=white>' . __('main.deuterium') . '<font></td><td width=50% align=right><font color=white>' . Format::number($FleetRow->resource_deuterium) . '<font></td></tr>';
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

	public static function getMissileRange(User $user)
	{
		if ($user->getTechLevel('impulse_motor') > 0) {
			return ($user->getTechLevel('impulse_motor') * 5) - 1;
		}

		return 0;
	}

	public static function getPhalanxRange($level)
	{
		$PhalanxRange = 0;

		if ($level > 1) {
			for ($Level = 2; $Level < $level + 1; $Level++) {
				$lvl = ($Level * 2) - 1;
				$PhalanxRange += $lvl;
			}
		}

		return $PhalanxRange;
	}
}
