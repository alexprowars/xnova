<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Helpers;
use Friday\Core\Lang;
use Xnova\Models\Fleet;
use Xnova\Models\Planet;
use Xnova\Queue;
use Xnova\Controller;
use Xnova\Vars;

/**
 * @RoutePrefix("/imperium")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ImperiumController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('imperium', 'xnova');

		$this->user->loadPlanet();
	}

	public function indexAction()
	{
		$r = [];
		$r1 = [];
		$parse = [];

		$build_hangar_full = [];

		$fleet_fly = [];

		$fleets = Fleet::find(['owner = ?0', 'bind' => [$this->user->getId()]]);

		foreach ($fleets as $fleet)
		{
			if (!isset($fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type]))
				$fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type] = [];

			if (!isset($fleet_fly[$fleet->splitTargetPosition() . ':' . $fleet->end_type]))
				$fleet_fly[$fleet->splitTargetPosition() . ':' . $fleet->end_type] = [];

			$fleetData = $fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr)
			{
				if (!isset($fleet_fly[$fleet->splitStartPosition().':'.$fleet->start_type][$shipId]))
					$fleet_fly[$fleet->splitStartPosition().':'.$fleet->start_type][$shipId] = 0;

				if (!isset($fleet_fly[$fleet->splitTargetPosition().':'.$fleet->end_type][$shipId]))
					$fleet_fly[$fleet->splitTargetPosition().':'.$fleet->end_type][$shipId] = 0;

				$fleet_fly[$fleet->splitStartPosition().':'.$fleet->start_type][$shipId] -= $shipArr['cnt'];

				if ($fleet->target_owner == $this->user->id)
					$fleet_fly[$fleet->splitTargetPosition().':'.$fleet->end_type][$shipId] += $shipArr['cnt'];

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

		$planets = Planet::find(["id_owner = " . $this->user->getId(), "order" => $this->user->getPlanetListSortQuery()]);

		$parse['mount'] = count($planets) + 3;

		foreach (Vars::getResources() as $res)
		{
			$parse['file_'.$res.'_t'] = 0;
			$parse['file_'.$res.'_ph_t'] = 0;
			$parse['file_'.$res] = [];
			$parse['file_'.$res.'_ph'] = [];
			$parse['file_'.$res.'_p'] = [];
		}

		foreach ($planets AS $planet)
		{
			$planet->assignUser($this->user);
			$planet->resourceUpdate(time(), true);

			$planet->field_max = $planet->getMaxFields();

			@$parse['file_images'] .= '<th width=75><a href="'.$this->url->getBaseUri().'overview/?chpl=' . $planet->id . '"><img src="'.$this->url->getBaseUri().'assets/images/planeten/small/s_' . $planet->image . '.jpg" border="0" height="75" width="75"></a></th>';
			@$parse['file_names'] .= "<th>" . $planet->name . "</th>";
			@$parse['file_coordinates'] .= "<th>[<a href=\"".$this->url->getBaseUri()."galaxy/".$planet->galaxy."/".$planet->system."/\">".$planet->galaxy.":".$planet->system.":".$planet->planet."</a>]</th>";
			@$parse['file_fields'] .= '<th>' . $planet->field_current . '/' . $planet->field_max . '</th>';
			@$parse['file_energy'] .= '<th>' . Helpers::pretty_number($planet->energy_max - abs($planet->energy_used)) . '</th>';
			@$parse['file_zar'] .= '<th><font color="#00ff00">' . round($planet->energy_ak / (250 * $planet->getBuildLevel('solar_plant')) * 100) . '</font>%</th>';

			@$parse['file_fields_c'] += $planet->field_current;
			@$parse['file_fields_t'] += $planet->field_max;

			@$parse['file_energy_t'] += $planet->energy_max - abs($planet->energy_used);

			foreach (Vars::getResources() as $res)
			{
				$parse['file_'.$res.'_t'] += $planet->{$res};
				$parse['file_'.$res.'_ph_t'] += $planet->{$res.'_perhour'};

				$parse['file_'.$res][] = $planet->{$res};
				$parse['file_'.$res.'_ph'][] = $planet->{$res.'_perhour'};
				$parse['file_'.$res.'_p'][] = $planet->getBuild($res.'_mine')['power'] * 10;
			}

			@$parse['file_solar_p'] .= '<th><font color="#00FF00">' . ($planet->getBuild('solar_plant')['power'] * 10) . '</font>%</th>';
			@$parse['file_fusion_p'] .= '<th><font color="#00FF00">' . ($planet->getBuild('fusion_plant')['power'] * 10) . '</font>%</th>';
			@$parse['file_solar2_p'] .= '<th><font color="#00FF00">' . ($planet->getBuild('solar_satelit')['power'] * 10) . '</font>%</th>';

			$build_hangar = [];

			$queueManager->loadQueue($planet->queue);

			foreach ($types AS $type)
			{
				if ($queueManager->getCount($type))
				{
					$queue = $queueManager->get($type);

					foreach ($queue AS $q)
					{
						if (!isset($build_hangar[$q['i']]) || Vars::getItemType($q['i']) == Vars::ITEM_TYPE_BUILING)
							$build_hangar[$q['i']]  = $q['l'];
						else
							$build_hangar[$q['i']] += $q['l'];

						if (!isset($build_hangar_full[$q['i']]) || Vars::getItemType($q['i']) == Vars::ITEM_TYPE_BUILING)
							$build_hangar_full[$q['i']]  = $q['l'];
						else
							$build_hangar_full[$q['i']] += $q['l'];
					}
				}
			}

			foreach ($this->registry->resource as $i => $res)
			{
				if (!isset($r[$i]))
					$r[$i] = '';
				if (!isset($r1[$i]))
					$r1[$i] = 0;

				if (Vars::getItemType($i) == Vars::ITEM_TYPE_BUILING)
				{
					$r[$i] .= ($planet->getBuildLevel($i) == 0) ? '<th>' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>' . $build_hangar[$i] . '</font>' : '-') . '</th>' : '<th>' . $planet->getBuildLevel($i) . '' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>-> ' . $build_hangar[$i] . '</font>' : '') . '</th>';
					if ($r1[$i] < $planet->getBuildLevel($i))
						$r1[$i] = $planet->getBuildLevel($i);
				}
				elseif (Vars::getItemType($i) == Vars::ITEM_TYPE_FLEET)
				{

					$r[$i] .= '<th>';

					if ($planet->getUnitCount($i) == 0 && !isset($build_hangar[$i]) && !isset($fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$i]))
						$r[$i] .= '-';
					else
					{
						if ($planet->getUnitCount($i) >= 0)
							$r[$i] .= $planet->getUnitCount($i);
						if (isset($build_hangar[$i]))
							$r[$i] .= ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>';
						if (isset($fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$i]))
							$r[$i] .= ' <font color=yellow>' . (($fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$i] > 0) ? '+' : '') . '' . $fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$i] . '</font>';
						$r[$i] .= '</th>';
					}

					$r1[$i] += $planet->getUnitCount($i);
				}
				elseif (Vars::getItemType($i) == Vars::ITEM_TYPE_DEFENSE)
				{
					$r[$i] .= ($planet->getUnitCount($i) == 0) ? '<th>' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>' : '-') . '</th>' : '<th>' . $planet->getUnitCount($i) . '' . ((isset($build_hangar[$i])) ? ' <font color=#00FF00>+' . $build_hangar[$i] . '</font>' : '') . '</th>';
					$r1[$i] += $planet->getUnitCount($i);
				}
			}
		}

		foreach (Vars::getResources() as $res)
		{
			$parse['file_'.$res.'_t'] = Helpers::pretty_number($parse['file_'.$res.'_t']);
			$parse['file_'.$res.'_ph_t'] = Helpers::pretty_number($parse['file_'.$res.'_ph_t']);
		}

		$parse['file_energy_t'] = Helpers::pretty_number($parse['file_energy_t']);
		$parse['file_kredits'] = Helpers::pretty_number($this->user->credits);

		$parse['building_row'] = '';
		$parse['fleet_row'] = '';
		$parse['defense_row'] = '';
		$parse['technology_row'] = '';

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_BUILING) as $i)
		{
			$parse['building_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $this->planet->getBuildLevel($i) . " (" . $r1[$i] . ")</th></tr>";
		}

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $i)
		{
			$parse['fleet_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $r1[$i] . "" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>+' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE) as $i)
		{
			$parse['defense_row'] .= "<tr><th colspan=\"2\">" . _getText('tech', $i) . "</th>" . $r[$i] . "<th>" . $r1[$i] . "" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>+' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_TECH) as $i)
		{
			$parse['technology_row'] .= "<tr><th colspan=\"" . ($parse['mount'] - 1) . "\">" . _getText('tech', $i) . "</th><th><font color=#FFFF00>" . $this->user->getTechLevel($i) . "</font>" . ((isset($build_hangar_full[$i])) ? ' <font color=#00FF00>-> ' . $build_hangar_full[$i] . '</font>' : '') . "</th></tr>";
		}

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Империя');
		$this->showTopPanel(false);
	}
}