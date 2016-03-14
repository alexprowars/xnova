<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Fleet;
use App\Helpers;
use App\Lang;
use App\Models\Planet;
use App\Queue;

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
		$r = [];
		$r1 = [];
		$parse = [];

		$build_hangar_full = [];

		$fleet_fly = [];

		/**
		 * @var $fleets \App\Models\Fleet[]
		 */
		$fleets = \App\Models\Fleet::find(['owner = ?0', 'bind' => [$this->user->getId()]]);

		foreach ($fleets as $fleet)
		{
			if (!isset($fleet_fly[$fleet->start_galaxy . ':' . $fleet->start_system . ':' . $fleet->start_planet . ':' . $fleet->start_type]))
				$fleet_fly[$fleet->start_galaxy . ':' . $fleet->start_system . ':' . $fleet->start_planet . ':' . $fleet->start_type] = [];

			if (!isset($fleet_fly[$fleet->end_galaxy . ':' . $fleet->end_system . ':' . $fleet->end_planet . ':' . $fleet->end_type]))
				$fleet_fly[$fleet->end_galaxy . ':' . $fleet->end_system . ':' . $fleet->end_planet . ':' . $fleet->end_type] = [];

			$fleetData = $fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr)
			{
				if (!isset($fleet_fly[$fleet->start_galaxy.':'.$fleet->start_system.':'.$fleet->start_planet.':'.$fleet->start_type][$shipId]))
				{
					$fleet_fly[$fleet->start_galaxy.':'.$fleet->start_system.':'.$fleet->start_planet.':'.$fleet->start_type][$shipId] = 0;
					$fleet_fly[$fleet->end_galaxy.':'.$fleet->end_system.':'.$fleet->end_planet.':'.$fleet->end_type][$shipId] = 0;
				}

				$fleet_fly[$fleet->start_galaxy.':'.$fleet->start_system.':'.$fleet->start_planet.':'.$fleet->start_type][$shipId] -= $shipArr['cnt'];

				if ($fleet->target_owner == $this->user->id)
					$fleet_fly[$fleet->end_galaxy.':'.$fleet->end_system.':'.$fleet->end_planet.':'.$fleet->end_type][$shipId] += $shipArr['cnt'];


				if ($fleet->target_owner == $this->user->id)
				{
					if (!isset($build_hangar_full[$shipId]))
						$build_hangar_full[$shipId] = 0;

					$build_hangar_full[$shipId] += $shipArr['cnt'];
				}
			}
		}

		$queueManager = new Queue();
		$types = $queueManager->getTypes();

		/**
		 * @var Planet[] $planets
		 */
		$planets = Planet::find(["id_owner = " . $this->user->getId(), "order" => trim(str_replace('ORDER BY', '', $this->user->getPlanetListSortQuery()))]);

		$parse['mount'] = count($planets) + 3;

		foreach ($planets AS $planet)
		{
			$planet->assignUser($this->user);
			$planet->PlanetResourceUpdate(time(), true);

			$planet->field_max = $planet->getMaxFields();

			@$parse['file_images'] .= '<th width=75><a href="/overview/?chpl=' . $planet->id . '"><img src="/assets/images/planeten/small/s_' . $planet->image . '.jpg" border="0" height="75" width="75"></a></th>';
			@$parse['file_names'] .= "<th>" . $planet->name . "</th>";
			@$parse['file_coordinates'] .= "<th>[<a href=\"/galaxy/".$planet->galaxy."/".$planet->system."/\">".$planet->galaxy.":".$planet->system.":".$planet->planet."</a>]</th>";
			@$parse['file_fields'] .= '<th>' . $planet->field_current . '/' . $planet->field_max . '</th>';
			@$parse['file_metal'] .= '<th>' . Helpers::pretty_number($planet->metal) . '</th>';
			@$parse['file_crystal'] .= '<th>' . Helpers::pretty_number($planet->crystal) . '</th>';
			@$parse['file_deuterium'] .= '<th>' . Helpers::pretty_number($planet->deuterium) . '</th>';
			@$parse['file_energy'] .= '<th>' . Helpers::pretty_number($planet->energy_max - abs($planet->energy_used)) . '</th>';
			@$parse['file_zar'] .= '<th><font color="#00ff00">' . round($planet->energy_ak / (250 * $planet->{$this->storage->resource[4]}) * 100) . '</font>%</th>';

			@$parse['file_fields_c'] += $planet->field_current;
			@$parse['file_fields_t'] += $planet->field_max;
			@$parse['file_metal_t'] += $planet->metal;
			@$parse['file_crystal_t'] += $planet->crystal;
			@$parse['file_deuterium_t'] += $planet->deuterium;
			@$parse['file_energy_t'] += $planet->energy_max - abs($planet->energy_used);

			@$parse['file_metal_ph'] .= '<th>' . Helpers::pretty_number($planet->metal_perhour) . '</th>';
			@$parse['file_crystal_ph'] .= '<th>' . Helpers::pretty_number($planet->crystal_perhour) . '</th>';
			@$parse['file_deuterium_ph'] .= '<th>' . Helpers::pretty_number($planet->deuterium_perhour) . '</th>';

			@$parse['file_metal_ph_t'] += $planet->metal_perhour;
			@$parse['file_crystal_ph_t'] += $planet->crystal_perhour;
			@$parse['file_deuterium_ph_t'] += $planet->deuterium_perhour;

			@$parse['file_metal_p'] .= '<th><font color="#00FF00">' . ($planet->metal_mine_porcent * 10) . '</font>%</th>';
			@$parse['file_crystal_p'] .= '<th><font color="#00FF00">' . ($planet->crystal_mine_porcent * 10) . '</font>%</th>';
			@$parse['file_deuterium_p'] .= '<th><font color="#00FF00">' . ($planet->deuterium_mine_porcent * 10) . '</font>%</th>';
			@$parse['file_solar_p'] .= '<th><font color="#00FF00">' . ($planet->solar_plant_porcent * 10) . '</font>%</th>';
			@$parse['file_fusion_p'] .= '<th><font color="#00FF00">' . ($planet->fusion_plant_porcent * 10) . '</font>%</th>';
			@$parse['file_solar2_p'] .= '<th><font color="#00FF00">' . ($planet->solar_satelit_porcent * 10) . '</font>%</th>';

			$build_hangar = [];

			$queueManager->loadQueue($planet->queue);

			foreach ($types AS $type)
			{
				if ($queueManager->getCount($type))
				{
					$queue = $queueManager->get($type);

					p($queue);

					foreach ($queue AS $q)
					{
						if (!isset($build_hangar[$q['i']]) || in_array($q['i'], $this->storage->reslist['build']))
							$build_hangar[$q['i']]  = $q['l'];
						else
							$build_hangar[$q['i']] += $q['l'];

						if (!isset($build_hangar_full[$q['i']]) || in_array($q['i'], $this->storage->reslist['build']))
							$build_hangar_full[$q['i']]  = $q['l'];
						else
							$build_hangar_full[$q['i']] += $q['l'];
					}
				}
			}

			foreach ($this->storage->resource as $i => $res)
			{
				if (!isset($r[$i]))
					$r[$i] = '';
				if (!isset($r1[$i]))
					$r1[$i] = 0;

				if (in_array($i, $this->storage->reslist['build']))
				{
					$r[$i] .= ($planet->{$this->storage->resource[$i]} == 0) ? '<th>' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>' . $build_hangar[$i] . '</font>' : '-') . '</th>' : '<th>' . $planet->{$this->storage->resource[$i]} . '' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>-> ' . $build_hangar[$i] . '</font>' : '') . '</th>';
					if ($r1[$i] < $planet->{$this->storage->resource[$i]})
						$r1[$i] = $planet->{$this->storage->resource[$i]};
				}
				elseif (in_array($i, $this->storage->reslist['fleet']))
				{

					$r[$i] .= '<th>';

					if ($planet->{$this->storage->resource[$i]} == 0 && !isset($build_hangar[$i]) && !isset($fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$i]))
						$r[$i] .= '-';
					else
					{
						if ($planet->{$this->storage->resource[$i]} >= 0)
							$r[$i] .= $planet->{$this->storage->resource[$i]};
						if (isset($build_hangar[$i]))
							$r[$i] .= ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>';
						if (isset($fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$i]))
							$r[$i] .= ' <font color=yellow>' . (($fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$i] > 0) ? '+' : '') . '' . $fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$i] . '</font>';
						$r[$i] .= '</th>';
					}

					$r1[$i] += $planet->{$this->storage->resource[$i]};
				}
				elseif (in_array($i, $this->storage->reslist['defense']))
				{
					$r[$i] .= ($planet->{$this->storage->resource[$i]} == 0) ? '<th>' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>' : '-') . '</th>' : '<th>' . $planet->{$this->storage->resource[$i]} . '' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>' : '') . '</th>';
					$r1[$i] += $planet->{$this->storage->resource[$i]};
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

		foreach ($this->storage->reslist['build'] as $i)
		{
			$parse['building_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $this->planet->{$this->storage->resource[$i]} . " (" . $r1[$i] . ")</th></tr>";
		}

		foreach ($this->storage->reslist['fleet'] as $i)
		{
			$parse['fleet_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $r1[$i] . "" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>+' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		foreach ($this->storage->reslist['defense'] as $i)
		{
			$parse['defense_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $r1[$i] . "" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>+' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		foreach ($this->storage->reslist['tech'] as $i)
		{
			$parse['technology_row'] .= "<tr><th colspan=\"" . ($parse['mount'] - 1) . "\">" . _getText('tech', $i) . "</th><th><font color=#FFFF00>" . $this->user->{$this->storage->resource[$i]} . "</font>" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>-> ' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Империя');
		$this->showTopPanel(false);
	}
}