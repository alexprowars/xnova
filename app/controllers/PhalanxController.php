<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Fleet;
use App\Helpers;
use App\Models\Fleet as FleetModel;
use App\Models\Planet;

class PhalanxController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		if ($this->user->vacation > 0)
			$this->message("Нет доступа!");
		
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
		
		if ($this->planet->planet_type != '3')
			$this->message("Вы можете использовать фалангу только на луне!", "Ошибка", "", 1, false);
		elseif ($this->planet->phalanx == '0')
			$this->message("Постройте сначало сенсорную фалангу", "Ошибка", "/overview/", 1, false);
		elseif ($this->planet->deuterium < $consomation)
			$this->message("<b>Недостаточно дейтерия для использования. Необходимо: 5000.</b>", "Ошибка", "", 2, false);
		elseif (($s <= $systemdol OR $s >= $systemgora) OR $g != $this->planet->galaxy)
			$this->message("Вы не можете сканировать данную планету. Недостаточный уровень сенсорной фаланги.", "Ошибка", "", 1, false);
		else
		{
			$this->planet->deuterium -= $consomation;
			$this->planet->update();
		}

		$planet = Planet::count(['galaxy = ?0 AND system = ?1 AND planet = ?2', 'bind' => [$g, $s, $i]]);
		
		if ($planet == 0)
			$this->message("Чит детектед! Режим бога активирован! Приятной игры!", "Ошибка", "", 1, false);
		
		$missiontype = [1 => 'Атаковать', 3 => 'Транспорт', 4 => 'Оставить', 5 => 'Удерживать', 6 => 'Шпионаж', 7 => 'Колонизировать', 8 => 'Переработать', 9 => 'Уничтожить'];

		$fq = FleetModel::find([
			'conditions' => 'owner != 1 AND ((start_galaxy = :galaxy: AND start_system = :system: AND start_planet = :planet: AND start_type != 3) OR (end_galaxy = :galaxy: AND end_system = :system: AND end_planet = :planet:))',
			'bind' => ['galaxy' => $g, 'system' => $s, 'planet' => $i],
			'order' => 'start_time asc'
		]);
		
		$list = [];
		
		foreach ($fq as $ii => $row)
		{
			if ($row->start_galaxy == $g && $row->start_system == $s && $row->start_planet == $i)
				$end = 0;
			else
				$end = 1;
		
			$timerek = $row->start_time;

			if ($row->mission != 6)
				$kolormisjido = 'lime';
			else
				$kolormisjido = 'orange';

			$g1 = $row->start_galaxy;
			$s1 = $row->start_system;
			$i1 = $row->start_planet;
			$t1 = $row->start_type;
		
			$g2 = $row->end_galaxy;
			$s2 = $row->end_system;
			$i2 = $row->end_planet;
			$t2 = $row->end_type;
		
			if ($t1 == 3)
				$type = "лун";
			else
				$type = "планет";

			if ($t2 == 3)
				$type2 = "лун";
			else
				$type2 = "планет";

			$nome = $row->owner_name;
			$nome2 = $row->target_owner_name;

			$item = '';
		
			if ($timerek > time() && $end == 1 && !($t1 == 3 && ($t2 == 2 || $t2 == 3)))
			{
				$item .= "<tr><th><div id=\"bxxfs$ii\" class=\"z\"></div><font color=\"lime\">" . $this->game->datezone("H:i:s", $row->start_time) . "</font> </th>";
		
				$Label = "fs";
				$Time = $row->start_time - time();
				$item .= Helpers::InsertJavaScriptChronoApplet($Label, $ii, $Time);
		
				$item .= "<th><font color=\"$kolormisjido\">Игрок ";
				$item .= "(" . Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user) . ")";
		
				$item .= " с " . $type . "ы " . $nome . " <font color=\"white\">[$g1:$s1:$i1]</font> летит на " . $type2 . "у " . $nome2 . " <font color=\"white\">[$g2:$s2:$i2]</font>. Задание:";
				$item .= " <font color=\"white\">{$missiontype[$row->mission]}</font></th>";
			}
		
			if ($row->mission <> 4 && $end == 0 && $t1 != 3)
			{
				$item .= "<tr><th><div id=\"bxxfe$ii\" class=\"z\"></div><font color=\"green\">" . $this->game->datezone("H:i:s", $row->end_time) . "</font></th>";
		
				$Label = "fe";
				$Time = $row->end_time - time();
				$item .= Helpers::InsertJavaScriptChronoApplet($Label, $ii, $Time);
		
				$item .= "<th><font color=\"$kolormisjido\">Игрок ";
				$item .= "(" . Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user) . ")";
		
				$item .= " с " . $type2 . "ы " . $nome2 . " <font color=\"white\">[$g2:$s2:$i2]</font> возвращается на " . $type . "у " . $nome . " <font color=\"white\">[$g1:$s1:$i1]</font>. Задание:";
				$item .= " <font color=\"white\">{$missiontype[$row->mission]}</font></th></tr>";
			}

			$list[] = $item;
		}

		$this->tag->setTitle('Сенсорная фаланга');
		$this->view->setVar('list', $list);
		$this->showTopPanel(false);
		$this->showLeftPanel(false);
	}
}