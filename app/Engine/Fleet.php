<?php

namespace App\Engine;

use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\Mission;
use App\Format;
use App\Models;
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

	public static function createFleetPopupedFleetLink(Models\Fleet $FleetRow, $Texte, $FleetType, ?User $user)
	{
		$FleetRec = $FleetRow->getShips();

		$FleetPopup = "<table width=200>";
		$r = 'javascript:;';

		$Total = 0;

		foreach ($FleetRec as $fleet) {
			$Total += $fleet['count'];
		}

		if ($user && $FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 2) {
			$FleetPopup .= "<tr><td width=100% align=center><font color=white>Нет информации<font></td></tr>";
		} elseif ($user && $FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 4) {
			$FleetPopup .= "<tr><td width=50% align=left><font color=white>Численность:<font></td><td width=50% align=right><font color=white>" . Format::number($Total) . "<font></td></tr>";
		} elseif ($user && $FleetRow->user_id != $user->id && $user->getTechLevel('spy') < 8) {
			foreach ($FleetRec as $id => $fleet) {
				$FleetPopup .= "<tr><td width=100% align=center colspan=2><font color=white>" . __('main.tech.' . $id) . "<font></td></tr>";
			}

			$FleetPopup .= "<tr><td width=50% align=left><font color=white>Численность:<font></td><td width=50% align=right><font color=white>" . Format::number($Total) . "<font></td></tr>";
		} else {
			if ($FleetRow->target_user_id == $user?->id && $FleetRow->mission == Mission::Attack) {
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

	public static function getFleetMissions($fleets, Coordinates $target = null, $youPlanet = false, $activePlanet = false, $assault = false)
	{
		if ($target === null) {
			$target = new Coordinates(1, 1, 1, PlanetType::PLANET);
		}

		$result = [];

		if ($target->getPlanet() == 16) {
			if (!(count($fleets) == 1 && isset($fleets[210]))) {
				$result[] = Mission::Expedition;
			}
		} elseif ($target->getType() == PlanetType::DEBRIS && isset($fleets[209])) {
			$result[] = Mission::Recycling;
		} elseif (in_array($target->getType(), [PlanetType::PLANET, PlanetType::MOON, PlanetType::MILITARY_BASE])) {
			if (isset($fleets[216]) && !$activePlanet && $target->getType() == PlanetType::PLANET) {
				$result[] = Mission::CreateBase;
			}

			if (isset($fleets[210]) && !$youPlanet) {
				$result[] = Mission::Spy;
			}

			if (isset($fleets[208]) && !$activePlanet) {
				$result[] = Mission::Colonization;
			}

			if (!$youPlanet && $activePlanet && !isset($fleets[208]) && !isset($fleets[209]) && !isset($fleets[216])) {
				$result[] = Mission::Attack;
			}

			if ($activePlanet && !$youPlanet && !(count($fleets) == 1 && isset($fleets[210]))) {
				$result[] = Mission::StayAlly;
			}

			if ($activePlanet && (isset($fleets[202]) || isset($fleets[203]))) {
				$result[] = Mission::Transport;
			}

			if ($youPlanet) {
				$result[] = Mission::Stay;
			}

			if ($assault && $activePlanet) {
				$result[] = Mission::Assault;
			}

			if ($target->getType() == PlanetType::MOON && isset($fleets[214]) && !$youPlanet && $activePlanet) {
				$result[] = Mission::Destruction;
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
