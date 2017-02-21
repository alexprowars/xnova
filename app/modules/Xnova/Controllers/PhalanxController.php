<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\MessageException;
use Xnova\Fleet;
use Xnova\Helpers;
use Xnova\Models\Fleet as FleetModel;
use Xnova\Models\Planet;
use Xnova\Controller;

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
			throw new ErrorException("Нет доступа!");
		
		$g = $this->request->getQuery('galaxy', 'int');
		$s = $this->request->getQuery('system', 'int');
		$i = $this->request->getQuery('planet', 'int');
		
		$consomation = 5000;
		
		if ($g < 1 || $g > $this->config->game->maxGalaxyInWorld)
			$g = $this->planet->galaxy;
		if ($s < 1 || $s > $this->config->game->maxSystemInGalaxy)
			$s = $this->planet->system;
		if ($i < 1 || $i > $this->config->game->maxPlanetInSystem)
			$i = $this->planet->planet;
		
		$systemdol 	= $this->planet->system - pow($this->planet->phalanx, 2);
		$systemgora = $this->planet->system + pow($this->planet->phalanx, 2);
		
		if ($this->planet->planet_type != 3)
			throw new MessageException("Вы можете использовать фалангу только на луне!", "Ошибка", "", 1, false);
		elseif ($this->planet->phalanx == 0)
			throw new MessageException("Постройте сначало сенсорную фалангу", "Ошибка", "/overview/", 1, false);
		elseif ($this->planet->deuterium < $consomation)
			throw new MessageException("<b>Недостаточно дейтерия для использования. Необходимо: 5000.</b>", "Ошибка", "", 2, false);
		elseif (($s <= $systemdol OR $s >= $systemgora) OR $g != $this->planet->galaxy)
			throw new MessageException("Вы не можете сканировать данную планету. Недостаточный уровень сенсорной фаланги.", "Ошибка", "", 1, false);
		else
		{
			$this->planet->deuterium -= $consomation;
			$this->planet->update();
		}

		$planet = Planet::count(['galaxy = ?0 AND system = ?1 AND planet = ?2', 'bind' => [$g, $s, $i]]);
		
		if ($planet == 0)
			throw new MessageException("Чит детектед! Режим бога активирован! Приятной игры!", "Ошибка", "", 1, false);

		$fleets = FleetModel::find([
			'conditions' => 'owner != 1 AND ((start_galaxy = :galaxy: AND start_system = :system: AND start_planet = :planet: AND start_type != 3) OR (end_galaxy = :galaxy: AND end_system = :system: AND end_planet = :planet:))',
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

			$item = '';
		
			if ($row->start_time > time() && $end && !($row->start_type == 3 && ($row->end_type == 2 || $row->end_type == 3)))
			{
				$item .= "<tr><th><div id=\"bxxfs".$ii."\" class=\"z\"></div><font color=\"lime\">" . $this->game->datezone("H:i:s", $row->start_time) . "</font> </th>";
				$item .= Helpers::InsertJavaScriptChronoApplet("fs", $ii, $row->start_time - time());
				$item .= "<th><font color=\"".$color."\">Игрок (" . Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user) . ")";
				$item .= " с " . $type . "ы " . $row->owner_name . " <font color=\"white\">[".$row->splitStartPosition()."]</font> летит на " . $type2 . "у " . $row->target_owner_name . " <font color=\"white\">[".$row->splitTargetPosition()."]</font>. Задание:";
				$item .= " <font color=\"white\">"._getText('type_mission', $row->mission)."</font></th>";
			}
		
			if ($row->mission <> 4 && !$end && $row->start_type != 3)
			{
				$item .= "<tr><th><div id=\"bxxfe".$ii."\" class=\"z\"></div><font color=\"green\">" . $this->game->datezone("H:i:s", $row->end_time) . "</font></th>";
				$item .= Helpers::InsertJavaScriptChronoApplet("fe", $ii, $row->end_time - time());
				$item .= "<th><font color=\"".$color."\">Игрок (" . Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user) . ")";
				$item .= " с " . $type2 . "ы " . $row->target_owner_name . " <font color=\"white\">[".$row->splitTargetPosition()."]</font> возвращается на " . $type . "у " . $row->owner_name . " <font color=\"white\">[".$row->splitStartPosition()."]</font>. Задание:";
				$item .= " <font color=\"white\">"._getText('type_mission', $row->mission)."</font></th></tr>";
			}

			if ($item != '')
				$list[] = $item;
		}

		$this->tag->setTitle('Сенсорная фаланга');
		$this->view->setVar('list', $list);
		$this->showTopPanel(false);
		$this->showLeftPanel(false);
	}
}