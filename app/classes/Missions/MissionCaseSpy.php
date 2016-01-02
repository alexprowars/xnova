<?php

namespace App\Missions;

use App\Fleet;
use App\FleetEngine;
use App\Helpers;
use App\Models\Planet;
use App\Models\User;

class MissionCaseSpy extends FleetEngine implements Mission
{
	function __construct($Fleet)
	{
			$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		$CurrentUser = $this->db->query("SELECT `spy_tech`, `rpg_technocrate` FROM game_users WHERE `id` = '" . $this->_fleet['fleet_owner'] . "';")->fetch();

		$TargetPlanet = Planet::findByCoords($this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet'], $this->_fleet['fleet_end_type']);

		if ($TargetPlanet->id_owner == 0)
		{
			$this->ReturnFleet();
			return false;
		}

		/**
		 * @var \App\Models\User $TargetUser
		 */
		$TargetUser = User::findFirst($TargetPlanet->id_owner);

		if (!isset($TargetUser->id))
		{
			$this->ReturnFleet();

			return false;
		}

		$TargetPlanet->assignUser($TargetUser);

		$CurrentSpyLvl = $CurrentUser['spy_tech'];
		if ($CurrentUser['rpg_technocrate'] > time())
			$CurrentSpyLvl += 2;

		$TargetSpyLvl = $TargetUser->spy_tech;
		if ($TargetUser->rpg_technocrate > time())
			$TargetSpyLvl += 2;

		// Обновление производства на планете
		// =============================================================================
		$TargetPlanet->PlanetResourceUpdate($this->_fleet['fleet_start_time']);
		// =============================================================================

		$LS = 0;

		$fleetData = Fleet::unserializeFleet($this->_fleet['fleet_array']);

		if (isset($fleetData[210]))
			$LS = $fleetData[210]['cnt'];

		if ($LS > 0)
		{
			$def = $this->db->query('SELECT fleet_array FROM game_fleets WHERE `fleet_end_galaxy` = ' . $this->_fleet['fleet_end_galaxy'] . ' AND `fleet_end_system` = ' . $this->_fleet['fleet_end_system'] . ' AND `fleet_end_type` = ' . $this->_fleet['fleet_end_type'] . ' AND `fleet_end_planet` = ' . $this->_fleet['fleet_end_planet'] . ' AND fleet_mess = 3');

			while ($defRow = $def->fetch())
			{
				$fleetData = Fleet::unserializeFleet($defRow['fleet_array']);

				foreach ($fleetData AS $Element => $Fleet)
				{
					if ($Element < 100)
						continue;

					$TargetPlanet->{$this->game->resource[$Element]} += $Fleet['cnt'];
				}
			}

			$ST = 0;

			$techDifference = abs($CurrentSpyLvl - $TargetSpyLvl);

			if ($TargetSpyLvl > $CurrentSpyLvl)
				$ST = ($LS - pow($techDifference, 2));
			if ($CurrentSpyLvl >= $TargetSpyLvl)
				$ST = ($LS + pow($techDifference, 2));

			$MaterialsInfo = $this->SpyTarget($TargetPlanet->toArray(), 0, _getText('sys_spy_maretials'));
			$SpyMessage = $MaterialsInfo['String'];

			$PlanetFleetInfo = $this->SpyTarget($TargetPlanet->toArray(), 1, _getText('sys_spy_fleet'));

			if ($ST >= 2)
			{
				$SpyMessage .= $PlanetFleetInfo['String'];
			}
			if ($ST >= 3)
			{
				$PlanetDefenInfo = $this->SpyTarget($TargetPlanet->toArray(), 2, _getText('sys_spy_defenses'));
				$SpyMessage .= $PlanetDefenInfo['String'];
			}
			if ($ST >= 5)
			{
				$PlanetBuildInfo = $this->SpyTarget($TargetPlanet->toArray(), 3, _getText('tech', 0));
				$SpyMessage .= $PlanetBuildInfo['String'];
			}
			if ($ST >= 7)
			{
				$TargetTechnInfo = $this->SpyTarget($TargetUser->toArray(), 4, _getText('tech', 100));
				$SpyMessage .= $TargetTechnInfo['String'];
			}
			if ($ST >= 8)
			{
				$TargetFleetLvlInfo = $this->SpyTarget($TargetUser->toArray(), 5, _getText('tech', 300));
				$SpyMessage .= $TargetFleetLvlInfo['String'];
			}
			if ($ST >= 9)
			{
				$TargetOfficierLvlInfo = $this->SpyTarget($TargetUser->toArray(), 6, _getText('tech', 600));
				$SpyMessage .= $TargetOfficierLvlInfo['String'];
			}

			$TargetForce = ($PlanetFleetInfo['Count'] * $LS) / 4;
			$TargetForce = min(100, max(0, $TargetForce));

			$TargetChances = rand(0, $TargetForce);
			$SpyerChances = rand(0, 100);

			if ($TargetChances <= $SpyerChances)
				$DestProba = sprintf(_getText('sys_mess_spy_lostproba'), $TargetChances);
			else
				$DestProba = "<font color=\"red\">" . _getText('sys_mess_spy_destroyed') . "</font>";

			$AttackLink = "<center>";
			$AttackLink .= "<a href=\"?set=fleet&galaxy=" . $this->_fleet['fleet_end_galaxy'] . "&system=" . $this->_fleet['fleet_end_system'] . "";
			$AttackLink .= "&planet=" . $this->_fleet['fleet_end_planet'] . "&planettype=" . $this->_fleet['fleet_end_type'] . "";
			$AttackLink .= "&target_mission=" . $this->_fleet['fleet_end_type'] . "";
			$AttackLink .= " \">" . _getText('type_mission', 1) . "";
			$AttackLink .= "</a></center>";

			$MessageEnd = "<center>" . $DestProba . "</center>";

			$fleet_link = '';

			if ($ST == 2)
				$res = $this->game->reslist['fleet'];
			elseif ($ST >= 3 && $ST <= 6)
				$res = array_merge($this->game->reslist['fleet'], $this->game->reslist['defense']);
			elseif ($ST >= 7)
				$res = array_merge($this->game->reslist['fleet'], $this->game->reslist['defense'], $this->game->reslist['tech']);
			else
				$res = array();

			foreach ($res AS $id)
			{
				if (isset($TargetPlanet->{$this->game->resource[$id]}) && $TargetPlanet->{$this->game->resource[$id]} > 0)
					$fleet_link .= $id . ',' . $TargetPlanet->{$this->game->resource[$id]} . '!' . ((isset($TargetUser->{'fleet_' . $id}) && $ST >= 8) ? $TargetUser->{'fleet_' . $id} : 0) . ';';

				if (isset($TargetUser->{$this->game->resource[$id]}) && $TargetUser->{$this->game->resource[$id]} > 0)
					$fleet_link .= $id . ',' . $TargetUser->{$this->game->resource[$id]} . '!' . (($id > 400 && isset($TargetUser->{$this->game->resource[$id - 50]}) && $ST >= 8) ? $TargetUser->{$this->game->resource[$id - 50]} : 0) . ';';
			}

			$MessageEnd .= "<center><a href=\"?set=sim&r=" . $fleet_link . "\" ".($this->config->view->get('openRaportInNewWindow', 0) ? 'target="_blank"' : '').">Симуляция</a></center>";
			$MessageEnd .= "<center><a href=\"#\" onclick=\"raport_to_bb('sp" . $this->_fleet['fleet_start_time'] . "')\">BB-код</a></center>";

			$SpyMessage = "<div id=\"sp" . $this->_fleet['fleet_start_time'] . "\">" . $SpyMessage . "</div><br />" . $MessageEnd . $AttackLink;

			$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 0, _getText('sys_mess_qg'), $SpyMessage);

			$TargetMessage  = _getText('sys_mess_spy_ennemyfleet') . " " . $this->_fleet['fleet_owner_name'] ." ";
			$TargetMessage .= Helpers::GetStartAdressLink($this->_fleet);
			$TargetMessage .= _getText('sys_mess_spy_seen_at') . " " . $TargetPlanet->name;
			$TargetMessage .= " [" . $TargetPlanet->galaxy . ":" . $TargetPlanet->system . ":" . $TargetPlanet->planet . "]. ";
			$TargetMessage .= sprintf(_getText('sys_mess_spy_lostproba'), $TargetChances) . ".";

			$this->game->sendMessage($TargetPlanet->id_owner, 0, $this->_fleet['fleet_start_time'], 0, _getText('sys_mess_spy_control'), $TargetMessage);

			if ($TargetChances > $SpyerChances)
			{
				$mission = new MissionCaseAttack($this->_fleet);
				$mission->TargetEvent();
			}
			else
				$this->ReturnFleet();
		}
		else
			$this->ReturnFleet();

		return true;
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		$this->RestoreFleetToPlanet();
		$this->KillFleet();
	}
}

?>