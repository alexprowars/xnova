<?php

namespace App;

use App\Entity\Coordinates;
use App\Models\User;

class Fleet extends Building
{
	public static function setShipsEngine(User $user)
	{
		$storage = Vars::getStorage();

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $Ship) {
			if (isset($storage->CombatCaps[$Ship]['engine_up']) && $user->getTechLevel($storage['CombatCaps'][$Ship]['engine_up']['tech']) >= $storage['CombatCaps'][$Ship]['engine_up']['lvl']) {
				$tmp = $storage['CombatCaps'];

				$tmp[$Ship]['type_engine']++;
				$tmp[$Ship]['speed'] = $tmp[$Ship]['engine_up']['speed'];

				unset($tmp[$Ship]['engine_up']);
				$storage['CombatCaps'] = $tmp;
			}
		}
	}

	public static function createFleetPopupedFleetLink(Models\Fleet $FleetRow, $Texte, $FleetType, User $user)
	{
		$FleetRec = $FleetRow->getShips();

		$FleetPopup = "<table width=200>";
		$r = 'javascript:;';

		$Total = 0;

		foreach ($FleetRec as $fleet) {
			$Total += $fleet['count'];
		}

		if ($FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 2) {
			$FleetPopup .= "<tr><td width=100% align=center><font color=white>Нет информации<font></td></tr>";
		} elseif ($FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 4) {
			$FleetPopup .= "<tr><td width=50% align=left><font color=white>Численность:<font></td><td width=50% align=right><font color=white>" . Format::number($Total) . "<font></td></tr>";
		} elseif ($FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 8) {
			foreach ($FleetRec as $id => $fleet) {
				$FleetPopup .= "<tr><td width=100% align=center colspan=2><font color=white>" . __('main.tech.' . $id) . "<font></td></tr>";
			}

			$FleetPopup .= "<tr><td width=50% align=left><font color=white>Численность:<font></td><td width=50% align=right><font color=white>" . Format::number($Total) . "<font></td></tr>";
		} else {
			if ($FleetRow->target_user_id == $user->id && $FleetRow->mission == 1) {
				$r = '/sim/';
			}

			foreach ($FleetRec as $id => $fleet) {
				$FleetPopup .= "<tr><td width=75% align=left><font color=white>" . __('main.tech.' . $id) . ":<font></td><td width=25% align=right><font color=white>" . Format::number($fleet['count']) . "<font></td></tr>";

				if ($r != 'javascript:;') {
					$r .= $id . ',' . $fleet['count'] . ';';
				}
			}
		}

		$FleetPopup .= "</table>";
		$FleetPopup .= "'>" . $Texte . "</a>";

		return "<a href='" . $r . "' class=\"tooltip " . $FleetType . "\" data-content='" . $FleetPopup;
	}

	public static function createFleetPopupedMissionLink($FleetRow, $Texte, $FleetType)
	{
		$FleetTotalC = $FleetRow->resource_metal + $FleetRow->resource_crystal + $FleetRow->resource_deuterium;

		if ($FleetTotalC != 0) {
			$FRessource = "<table width=200>";
			$FRessource .= "<tr><td width=50% align=left><font color=white>" . __('main.Metal') . "<font></td><td width=50% align=right><font color=white>" . Format::number($FleetRow->resource_metal) . "<font></td></tr>";
			$FRessource .= "<tr><td width=50% align=left><font color=white>" . __('main.Crystal') . "<font></td><td width=50% align=right><font color=white>" . Format::number($FleetRow->resource_crystal) . "<font></td></tr>";
			$FRessource .= "<tr><td width=50% align=left><font color=white>" . __('main.Deuterium') . "<font></td><td width=50% align=right><font color=white>" . Format::number($FleetRow->resource_deuterium) . "<font></td></tr>";
			$FRessource .= "</table>";
		} else {
			$FRessource = "";
		}

		if ($FRessource <> "") {
			$MissionPopup = "<a href='javascript:;' data-content='" . $FRessource . "' class=\"tooltip " . $FleetType . "\">" . $Texte . "</a>";
		} else {
			$MissionPopup = $Texte . "";
		}

		return $MissionPopup;
	}

	public static function getFleetMissions($fleetArray, Coordinates $target = null, $isYouPlanet = false, $isActivePlanet = false, $isAcs = false)
	{
		if ($target === null) {
			$target = new Coordinates(1, 1, 1, 1);
		}

		$result = [];

		if ($target->getPlanet() == 16) {
			if (!(count($fleetArray) == 1 && isset($fleetArray[210]))) {
				$result[] = 15;
			}
		} else {
			if ($target->getType() == 2 && isset($fleetArray[209])) {
				$result[] = 8; // Переработка
			} elseif ($target->getType() == 1 || $target->getType() == 3 || $target->getType() == 5) {
				if (isset($fleetArray[216]) && !$isActivePlanet && $target->getType() == 1) {
					$result[] = 10; // Создать базу
				}

				if (isset($fleetArray[210]) && !$isYouPlanet) {
					$result[] = 6; // Шпионаж
				}

				if (isset($fleetArray[208]) && !$isActivePlanet) {
					$result[] = 7; // Колонизировать
				}

				if (!$isYouPlanet && $isActivePlanet && !isset($fleetArray[208]) && !isset($fleetArray[209]) && !isset($fleetArray[216])) {
					$result[] = 1; // Атаковать
				}

				if ($isActivePlanet && !$isYouPlanet && !(count($fleetArray) == 1 && isset($fleetArray[210]))) {
					$result[] = 5; // Удерживать
				}

				if ($isActivePlanet && (isset($fleetArray[202]) || isset($fleetArray[203]))) {
					$result[] = 3; // Транспорт
				}

				if ($isYouPlanet) {
					$result[] = 4; // Оставить
				}

				if ($isAcs > 0 && $isActivePlanet) {
					$result[] = 2; // Объединить
				}

				if ($target->getType() == 3 && isset($fleetArray[214]) && !$isYouPlanet && $isActivePlanet) {
					$result[] = 9;
				}
			}
		}

		return $result;
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
