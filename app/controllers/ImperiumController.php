<?php

namespace App\Controllers;

use App\Helpers;
use App\Lang;

class ImperiumController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		Lang::includeLang('imperium');

		$this->user->loadPlanet();
	}

	public function indexAction()
	{
		global $resource, $reslist;

		$r = array();
		$r1 = array();
		$parse = array();

		$build_hangar_full = array();

		$fleet_fly = array();

		$fleets = $this->db->query("SELECT * FROM game_fleets WHERE fleet_owner = " . $this->user->getId() . "");

		while ($fleet = $fleets->fetch())
		{
			if (!isset($fleet_fly[$fleet['fleet_start_galaxy'] . ':' . $fleet['fleet_start_system'] . ':' . $fleet['fleet_start_planet'] . ':' . $fleet['fleet_start_type']]))
				$fleet_fly[$fleet['fleet_start_galaxy'] . ':' . $fleet['fleet_start_system'] . ':' . $fleet['fleet_start_planet'] . ':' . $fleet['fleet_start_type']] = array();

			if ($fleet['fleet_target_owner'] == $this->user->id && !isset($fleet_fly[$fleet['fleet_end_galaxy'] . ':' . $fleet['fleet_end_system'] . ':' . $fleet['fleet_end_planet'] . ':' . $fleet['fleet_end_type']]))
				$fleet_fly[$fleet['fleet_end_galaxy'] . ':' . $fleet['fleet_end_system'] . ':' . $fleet['fleet_end_planet'] . ':' . $fleet['fleet_end_type']] = array();

			$fleetData = unserializeFleet($fleet['fleet_array']);

			foreach ($fleetData as $shipId => $shipArr)
			{
				if (!isset($fleet_fly[$fleet['fleet_start_galaxy'].':'.$fleet['fleet_start_system'].':'.$fleet['fleet_start_planet'].':'.$fleet['fleet_start_type']][$shipId]))
				{
					$fleet_fly[$fleet['fleet_start_galaxy'].':'.$fleet['fleet_start_system'].':'.$fleet['fleet_start_planet'].':'.$fleet['fleet_start_type']][$shipId] = 0;

					if ($fleet['fleet_target_owner'] == $this->user->id)
						$fleet_fly[$fleet['fleet_end_galaxy'].':'.$fleet['fleet_end_system'].':'.$fleet['fleet_end_planet'].':'.$fleet['fleet_end_type']][$shipId] = 0;
				}

				$fleet_fly[$fleet['fleet_start_galaxy'].':'.$fleet['fleet_start_system'].':'.$fleet['fleet_start_planet'].':'.$fleet['fleet_start_type']][$shipId] -= $shipArr['cnt'];

				if ($fleet['fleet_target_owner'] == $this->user->id)
					$fleet_fly[$fleet['fleet_end_galaxy'].':'.$fleet['fleet_end_system'].':'.$fleet['fleet_end_planet'].':'.$fleet['fleet_end_type']][$shipId] += $shipArr['cnt'];


				if ($fleet['fleet_target_owner'] == $this->user->id)
				{
					if (!isset($build_hangar_full[$shipId]))
						$build_hangar_full[$shipId] = 0;

					$build_hangar_full[$shipId] += $shipArr['cnt'];
				}
			}
		}

		$queueManager = new queueManager();
		$types = $queueManager->getTypes();

		$imperium = new planet();
		$imperium->load_user_info(user::get());

		$planetsrow = $this->db->query("SELECT * FROM game_planets WHERE `id_owner` = '" . $this->user->getId() . "' ".$this->user->getPlanetListSortQuery()."");

		$parse['mount'] = $planetsrow->numRows() + 3;

		while ($p = $planetsrow->fetch())
		{
			$imperium->load_from_array($p);
			$imperium->PlanetResourceUpdate(time(), true);

			$p = $imperium->data;

			$p['field_max'] = CalculateMaxPlanetFields($p);

			@$parse['file_images'] .= '<th width=75><a href="?set=overview&cp=' . $p['id'] . '&amp;re=0"><img src="' . DPATH . 'planeten/small/s_' . $p['image'] . '.jpg" border="0" height="75" width="75"></a></th>';
			@$parse['file_names'] .= "<th>" . $p['name'] . "</th>";
			@$parse['file_coordinates'] .= "<th>[<a href=\"?set=galaxy&r=3&galaxy=".$p['galaxy']."&system=".$p['system']."\">".$p['galaxy'].":".$p['system'].":".$p['planet']."</a>]</th>";
			@$parse['file_fields'] .= '<th>' . $p['field_current'] . '/' . $p['field_max'] . '</th>';
			@$parse['file_metal'] .= '<th>' . Helpers::pretty_number($p['metal']) . '</th>';
			@$parse['file_crystal'] .= '<th>' . Helpers::pretty_number($p['crystal']) . '</th>';
			@$parse['file_deuterium'] .= '<th>' . Helpers::pretty_number($p['deuterium']) . '</th>';
			@$parse['file_energy'] .= '<th>' . Helpers::pretty_number($p['energy_max'] - abs($p['energy_used'])) . '</th>';
			@$parse['file_zar'] .= '<th><font color="#00ff00">' . round($p['energy_ak'] / (250 * $p[$resource[4]]) * 100) . '</font>%</th>';

			@$parse['file_fields_c'] += $p['field_current'];
			@$parse['file_fields_t'] += $p['field_max'];
			@$parse['file_metal_t'] += $p['metal'];
			@$parse['file_crystal_t'] += $p['crystal'];
			@$parse['file_deuterium_t'] += $p['deuterium'];
			@$parse['file_energy_t'] += $p['energy_max'] - abs($p['energy_used']);

			@$parse['file_metal_ph'] .= '<th>' . Helpers::pretty_number($p['metal_perhour']) . '</th>';
			@$parse['file_crystal_ph'] .= '<th>' . Helpers::pretty_number($p['crystal_perhour']) . '</th>';
			@$parse['file_deuterium_ph'] .= '<th>' . Helpers::pretty_number($p['deuterium_perhour']) . '</th>';

			@$parse['file_metal_ph_t'] += $p['metal_perhour'];
			@$parse['file_crystal_ph_t'] += $p['crystal_perhour'];
			@$parse['file_deuterium_ph_t'] += $p['deuterium_perhour'];

			@$parse['file_metal_p'] .= '<th><font color="#00FF00">' . ($p['metal_mine_porcent'] * 10) . '</font>%</th>';
			@$parse['file_crystal_p'] .= '<th><font color="#00FF00">' . ($p['crystal_mine_porcent'] * 10) . '</font>%</th>';
			@$parse['file_deuterium_p'] .= '<th><font color="#00FF00">' . ($p['deuterium_mine_porcent'] * 10) . '</font>%</th>';
			@$parse['file_solar_p'] .= '<th><font color="#00FF00">' . ($p['solar_plant_porcent'] * 10) . '</font>%</th>';
			@$parse['file_fusion_p'] .= '<th><font color="#00FF00">' . ($p['fusion_plant_porcent'] * 10) . '</font>%</th>';
			@$parse['file_solar2_p'] .= '<th><font color="#00FF00">' . ($p['solar_satelit_porcent'] * 10) . '</font>%</th>';

			$build_hangar = array();

			$queueManager->loadQueue($p['queue']);

			foreach ($types AS $type)
			{
				if ($queueManager->getCount($type))
				{
					$queue = $queueManager->get($type);

					foreach ($queue AS $q)
					{
						if (!isset($build_hangar[$q['i']]))
							$build_hangar[$q['i']]  = $q['l'];
						else
							$build_hangar[$q['i']] += $q['l'];

						if (!isset($build_hangar_full[$q['i']]))
							$build_hangar_full[$q['i']]  = $q['l'];
						else
							$build_hangar_full[$q['i']] += $q['l'];
					}
				}
			}

			foreach ($resource as $i => $res)
			{

				if (!isset($r[$i]))
					$r[$i] = '';
				if (!isset($r1[$i]))
					$r1[$i] = 0;

				if (in_array($i, $reslist['build']))
				{
					$r[$i] .= ($p[$resource[$i]] == 0) ? '<th>' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>' . $build_hangar[$i] . '</font>' : '-') . '</th>' : '<th>' . $p[$resource[$i]] . '' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>-> ' . $build_hangar[$i] . '</font>' : '') . '</th>';
					if ($r1[$i] < $p[$resource[$i]])
						$r1[$i] = $p[$resource[$i]];
				}
				elseif (in_array($i, $reslist['fleet']))
				{

					$r[$i] .= '<th>';

					if ($p[$resource[$i]] == 0 && !isset($build_hangar[$i]) && !isset($fleet_fly[$p['galaxy'] . ':' . $p['system'] . ':' . $p['planet'] . ':' . $p['planet_type']][$i]))
						$r[$i] .= '-';
					else
					{
						if ($p[$resource[$i]] >= 0)
							$r[$i] .= $p[$resource[$i]];
						if (isset($build_hangar[$i]))
							$r[$i] .= ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>';
						if (isset($fleet_fly[$p['galaxy'] . ':' . $p['system'] . ':' . $p['planet'] . ':' . $p['planet_type']][$i]))
							$r[$i] .= ' <font color=yellow>' . (($fleet_fly[$p['galaxy'] . ':' . $p['system'] . ':' . $p['planet'] . ':' . $p['planet_type']][$i] > 0) ? '+' : '') . '' . $fleet_fly[$p['galaxy'] . ':' . $p['system'] . ':' . $p['planet'] . ':' . $p['planet_type']][$i] . '</font>';
						$r[$i] .= '</th>';
					}

					$r1[$i] += $p[$resource[$i]];
				}
				elseif (in_array($i, $reslist['defense']))
				{
					$r[$i] .= ($p[$resource[$i]] == 0) ? '<th>' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>' : '-') . '</th>' : '<th>' . $p[$resource[$i]] . '' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>' : '') . '</th>';
					$r1[$i] += $p[$resource[$i]];
				}
			}
		}

		$parse['file_metal_t'] = Helpers::pretty_number($parse['file_metal_t']);
		$parse['file_crystal_t'] = Helpers::pretty_number($parse['file_crystal_t']);
		$parse['file_deuterium_t'] = Helpers::pretty_number($parse['file_deuterium_t']);
		$parse['file_energy_t'] = Helpers::pretty_number($parse['file_energy_t']);

		$parse['file_metal_ph_t'] = Helpers::pretty_number($parse['file_metal_ph_t']);
		$parse['file_crystal_ph_t'] = Helpers::pretty_number($parse['file_crystal_ph_t']);
		$parse['file_deuterium_ph_t'] = Helpers::pretty_number($parse['file_deuterium_ph_t']);

		$parse['file_kredits'] = Helpers::pretty_number($this->user->credits);

		$parse['building_row'] = '';
		$parse['fleet_row'] = '';
		$parse['defense_row'] = '';
		$parse['technology_row'] = '';

		foreach ($reslist['build'] as $i)
		{
			$parse['building_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $this->planet->data[$resource[$i]] . " (" . $r1[$i] . ")</th></tr>";
		}

		foreach ($reslist['fleet'] as $i)
		{
			$parse['fleet_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $r1[$i] . "" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>+' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		foreach ($reslist['defense'] as $i)
		{
			$parse['defense_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $r1[$i] . "" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>+' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		foreach ($reslist['tech'] as $i)
		{
			$parse['technology_row'] .= "<tr><th colspan=\"" . ($parse['mount'] - 1) . "\">" . _getText('tech', $i) . "</th><th><font color=#FFFF00>" . $this->user->data[$resource[$i]] . "</font>" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>-> ' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		$this->view->pick('imperium');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Империя');
		$this->showTopPanel(false);
	}
}