<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Fleet;
use Xnova\Models\Fleet as FleetModel;
use Xnova\Models\Planet;
use Xnova\Controller;
use Xnova\Request;

/**
 * @RoutePrefix("/phalanx")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class PhalanxController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		if ($this->user->vacation > 0)
			throw new PageException("Нет доступа!");
		
		$g = (int) $this->request->getQuery('galaxy', 'int');
		$s = (int) $this->request->getQuery('system', 'int');
		$i = (int) $this->request->getQuery('planet', 'int');
		
		$consomation = 5000;
		
		if ($g < 1 || $g > $this->config->game->maxGalaxyInWorld)
			$g = $this->planet->galaxy;
		if ($s < 1 || $s > $this->config->game->maxSystemInGalaxy)
			$s = $this->planet->system;
		if ($i < 1 || $i > $this->config->game->maxPlanetInSystem)
			$i = $this->planet->planet;

		$phalanx = $this->planet->getBuildLevel('phalanx');
		
		$systemdol 	= $this->planet->system - pow($phalanx, 2);
		$systemgora = $this->planet->system + pow($phalanx, 2);
		
		if ($this->planet->planet_type != 3)
			throw new PageException("Вы можете использовать фалангу только на луне!");
		elseif ($phalanx == 0)
			throw new PageException("Постройте сначало сенсорную фалангу");
		elseif ($this->planet->deuterium < $consomation)
			throw new PageException("<b>Недостаточно дейтерия для использования. Необходимо: ".$consomation.".</b>");
		elseif (($s <= $systemdol OR $s >= $systemgora) OR $g != $this->planet->galaxy)
			throw new PageException("Вы не можете сканировать данную планету. Недостаточный уровень сенсорной фаланги.");

		$this->planet->deuterium -= $consomation;
		$this->planet->update();

		$planet = Planet::count(['galaxy = ?0 AND system = ?1 AND planet = ?2', 'bind' => [$g, $s, $i]]);
		
		if ($planet == 0)
			throw new RedirectException("Чит детектед! Режим бога активирован! Приятной игры!", "");

		$fleets = FleetModel::find([
			'conditions' => '((start_galaxy = :galaxy: AND start_system = :system: AND start_planet = :planet: AND start_type != 3) OR (end_galaxy = :galaxy: AND end_system = :system: AND end_planet = :planet:))',
			'bind' => ['galaxy' => $g, 'system' => $s, 'planet' => $i],
			'order' => 'start_time asc'
		]);
		
		$list = [];
		
		foreach ($fleets as $ii => $row)
		{
			$end = !($row->start_galaxy == $g && $row->start_system == $s && $row->start_planet == $i);

			$color = ($row->mission != 6) ? 'lime' : 'orange';

			if ($row->start_type == 3)
				$type = "лун";
			else
				$type = "планет";

			if ($row->end_type == 3)
				$type2 = "лун";
			else
				$type2 = "планет";

			if ($row->start_time > time() && $end && !($row->start_type == 3 && ($row->end_type == 2 || $row->end_type == 3)))
			{
				$list[] = [
					'time' => (int) $row->start_time,
					'fleet' => Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user),
					'type_1' => $type.'ы',
					'type_2' => $type2.'у',
					'planet_name' => $row->owner_name,
					'planet_position' => $row->splitStartPosition(),
					'target_name' => $row->target_owner_name,
					'target_position' => $row->splitTargetPosition(),
					'mission' => _getText('type_mission', $row->mission),
					'color' => $color,
					'direction' => 1
				];
			}
		
			if ($row->mission <> 4 && !$end && $row->start_type != 3)
			{
				$list[] = [
					'time' => (int) $row->end_time,
					'fleet' => Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user),
					'type_1' => $type2.'ы',
					'type_2' => $type.'у',
					'planet_name' => $row->target_owner_name,
					'planet_position' => $row->splitTargetPosition(),
					'target_name' => $row->owner_name,
					'target_position' => $row->splitStartPosition(),
					'mission' => _getText('type_mission', $row->mission),
					'color' => $color,
					'direction' => 2
				];
			}
		}

		Request::addData('page', [
			'items' => $list
		]);

		$this->tag->setTitle('Сенсорная фаланга');

		$this->showTopPanel(false);
		$this->showLeftPanel(false);
	}
}