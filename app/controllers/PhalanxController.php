<?php

namespace App\Controllers;

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

		$galaktyka = $this->planet->galaxy;
		$system = $this->planet->system;
		$planeta = $this->planet->planet;
		
		$g = intval($_GET["galaxy"]);
		$s = intval($_GET["system"]);
		$i = intval($_GET["planet"]);
		
		$deuterium = $this->planet->deuterium;
		$phalangelvl = $this->planet->phalanx;
		$consomation = 5000;
		$deuteriumtotal = $deuterium - $consomation;
		
		if ($g < 1 || $g > $this->config->game->maxGalaxyInWorld)
			$g = $galaktyka;
		if ($s < 1 || $s > $this->config->game->maxSystemInGalaxy)
			$s = $system;
		if ($i < 1 || $i > $this->config->game->maxPlanetInSystem)
			$i = $planeta;
		
		$systemdol = $system - pow($phalangelvl, 2);
		$systemgora = $system + pow($phalangelvl, 2);
		
		if ($this->planet->planet_type != '3')
			$this->message("Вы можете использовать фалангу только на луне!", "Ошибка", "", 1, false);
		elseif ($this->planet->phalanx == '0')
			$this->message("Постройте сначало сенсорную фалангу", "Ошибка", "?set=overview", 1, false);
		elseif ($deuterium < $consomation)
			$this->message("<b>Недостаточно дейтерия для использования. Необходимо: 5000.</b>", "Ошибка", "", 2, false);
		elseif (($s <= $systemdol OR $s >= $systemgora) OR $g != $this->planet->galaxy)
			$this->message("Вы не можете сканировать данную планету. Недостаточный уровень сенсорной фаланги.", "Ошибка", "", 1, false);
		else
			$this->db->query("UPDATE game_planets SET deuterium = " . $deuteriumtotal . " WHERE id = '".$this->user->planet_current."'");

		$planet = $this->db->query("SELECT * FROM game_planets WHERE galaxy = " . $g . " AND system = " . $s . " AND planet = " . $i . "");
		
		if ($planet->numRows() == 0)
			$this->message("Чит детектед! Режим бога активирован! Приятной игры!", "Ошибка", "", 1, false);
		
		$missiontype = array(1 => 'Атаковать', 3 => 'Транспорт', 4 => 'Оставить', 5 => 'Удерживать', 6 => 'Шпионаж', 7 => 'Колонизировать', 8 => 'Переработать', 9 => 'Уничтожить');
		
		$fq = $this->db->query("SELECT * FROM game_fleets WHERE fleet_owner != 1 AND (( fleet_start_galaxy = '" . $g . "' AND fleet_start_system = '" . $s . "' AND fleet_start_planet = '" . $i . "' AND fleet_start_type != 3 ) OR ( fleet_end_galaxy = '" . $g . "' AND fleet_end_system = '" . $s . "' AND fleet_end_planet = '" . $i . "' )) ORDER BY `fleet_start_time`");
		
		$parse = array();
		$ii = 0;
		
		$parse['manobras'] = '';
		
		while ($row = $fq->fetch())
		{
			if ($row['fleet_start_galaxy'] == $g && $row['fleet_start_system'] == $s && $row['fleet_start_planet'] == $i)
				$end = 0;
			else
				$end = 1;
		
			$timerek = $row['fleet_start_time'];

			if ($row['fleet_mission'] != 6)
			{
				$kolormisjido = 'lime';
			}
			else
			{
				$kolormisjido = 'orange';
			}
		
			$g1 = $row['fleet_start_galaxy'];
			$s1 = $row['fleet_start_system'];
			$i1 = $row['fleet_start_planet'];
			$t1 = $row['fleet_start_type'];
		
			$g2 = $row['fleet_end_galaxy'];
			$s2 = $row['fleet_end_system'];
			$i2 = $row['fleet_end_planet'];
			$t2 = $row['fleet_end_type'];
		
			if ($t1 == '3')
			{
				$type = "лун";
			}
			else
			{
				$type = "планет";
			}
		
			if ($t2 == '3')
			{
				$type2 = "лун";
			}
			else
			{
				$type2 = "планет";
			}
		
			$nome = $row['fleet_owner_name'];
			$nome2 = $row['fleet_target_owner_name'];
		
			if ($timerek > time() && $end == 1 && !($t1 == 3 && ($t2 == 2 || $t2 == 3)))
			{
				$parse['manobras'] .= "<tr><th><div id=\"bxxfs$ii\" class=\"z\"></div><font color=\"lime\">" . $this->game->datezone("H:i:s", $row['fleet_start_time']) . "</font> </th>";
		
				$Label = "fs";
				$Time = $row['fleet_start_time'] - time();
				$parse['manobras'] .= InsertJavaScriptChronoApplet($Label, $ii, $Time);
		
				$parse['manobras'] .= "<th><font color=\"$kolormisjido\">Игрок ";
				$parse['manobras'] .= "(" . CreateFleetPopupedFleetLink($row, 'флот', '') . ")";
		
				$parse['manobras'] .= " с " . $type . "ы " . $nome . " <font color=\"white\">[$g1:$s1:$i1]</font> летит на " . $type2 . "у " . $nome2 . " <font color=\"white\">[$g2:$s2:$i2]</font>. Задание:";
				$parse['manobras'] .= " <font color=\"white\">{$missiontype[$row['fleet_mission']]}</font></th>";
		
				$ii++;
			}
		
			if ($row['fleet_mission'] <> 4 && $end == 0 && $t1 != 3)
			{
				$parse['manobras'] .= "<tr><th><div id=\"bxxfe$ii\" class=\"z\"></div><font color=\"green\">" . $this->game->datezone("H:i:s", $row['fleet_end_time']) . "</font></th>";
		
				$Label = "fe";
				$Time = $row['fleet_end_time'] - time();
				$parse['manobras'] .= InsertJavaScriptChronoApplet($Label, $ii, $Time);
		
				$parse['manobras'] .= "<th><font color=\"$kolormisjido\">Игрок ";
				$parse['manobras'] .= "(" . CreateFleetPopupedFleetLink($row, 'флот', '') . ")";
		
				$parse['manobras'] .= " с " . $type2 . "ы " . $nome2 . " <font color=\"white\">[$g2:$s2:$i2]</font> возвращается на " . $type . "у " . $nome . " <font color=\"white\">[$g1:$s1:$i1]</font>. Задание:";
				$parse['manobras'] .= " <font color=\"white\">{$missiontype[$row['fleet_mission']]}</font></th></tr>";
		
				$ii++;
			}
		}
		
		if ($ii > 0)
		{
			$this->view->pick('phalanx');
			$this->view->setVar('parse', $parse);
		
			$html = '';
		}
		else
		{
			$html = "<center><table width=519><tr><td class=c colspan=7>Нет флотов.</td></tr><th>На этой планете нет движения флотов.</th></table></center>";
		}

		$this->tag->setTitle('Сенсорная фаланга');
		$this->view->setVar('html', $html);
		$this->showTopPanel(false);
		$this->showLeftPanel(false);
	}
}

?>