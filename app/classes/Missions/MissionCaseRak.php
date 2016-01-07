<?php

namespace App\Missions;

use App\FleetEngine;

class MissionCaseRak extends FleetEngine implements Mission
{
	function __construct($Fleet)
	{
			$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		$this->KillFleet();

		$PlanetRow = $this->db->query("SELECT * FROM game_planets WHERE galaxy = '" . $this->_fleet['fleet_end_galaxy'] . "' AND system = '" . $this->_fleet['fleet_end_system'] . "' AND planet = '" . $this->_fleet['fleet_end_planet'] . "' AND planet_type = 1")->fetch();

		$Defender = $this->db->query("SELECT `defence_tech`  FROM game_users WHERE id = '" . $this->_fleet['fleet_target_owner'] . "'")->fetch();
		$Attacker = $this->db->query("SELECT `military_tech` FROM game_users WHERE id = '" . $this->_fleet['fleet_owner'] . "'")->fetch();

		if (isset($PlanetRow['id']) && isset($Defender['defence_tech']))
		{
			// Массивы параметров
			$ids = array(0 => 401, 1 => 402, 2 => 403, 3 => 404, 4 => 405, 5 => 406, 6 => 407, 7 => 408, 8 => 503, 9 => 502);

			$message = '';

			$Raks = 0;
			$Primary = 401;

			$temp = explode(';', $this->_fleet['fleet_array']);
			foreach ($temp as $temp2)
			{
				$temp2 = explode(',', $temp2);
				$temp3 = explode('!', $temp2[1]);

				if ($temp2[0] == 503)
				{
					$Raks = $temp3[0];
					$Primary = $ids[$temp3[1]];
				}
			}

			$TargetDefensive = array();

			foreach ($this->game->reslist['defense'] as $Element)
			{
				$TargetDefensive[$Element] = $PlanetRow[$this->game->resource[$Element]];
			}

			if ($PlanetRow['interceptor_misil'] >= $Raks)
			{
				$message .= 'Вражеская ракетная атака была отбита ракетами-перехватчиками<br>';

				$this->db->query("UPDATE game_planets SET interceptor_misil = interceptor_misil - " . $Raks . " WHERE id = " . $PlanetRow['id']);
			}
			else
			{
				$message .= 'Произведена межпланетная атака (' . $Raks . ' ракет) с ' . $this->_fleet['fleet_owner_name'] . ' <a href="/galaxy/' . $this->_fleet['fleet_start_galaxy'] . '/' . $this->_fleet['fleet_start_system'] . '/">[' . $this->_fleet['fleet_start_galaxy'] . ':' . $this->_fleet['fleet_start_system'] . ':' . $this->_fleet['fleet_start_planet'] . ']</a>';
				$message .= ' на планету ' . $this->_fleet['fleet_target_owner_name'] . ' <a href="/galaxy/' . $this->_fleet['fleet_end_galaxy'] . '/' . $this->_fleet['fleet_end_system'] . '/">[' . $this->_fleet['fleet_end_galaxy'] . ':' . $this->_fleet['fleet_end_system'] . ':' . $this->_fleet['fleet_end_planet'] . ']</a>.<br><br>';

				if ($PlanetRow['interceptor_misil'] > 0)
				{
					$message .= $PlanetRow['interceptor_misil'] . " ракеты-перехватчика частично отбили атаку вражеских межпланетных ракет.<br>";
					$this->db->query("UPDATE game_planets SET interceptor_misil = 0 WHERE id = " . $PlanetRow['id']);
				}

				$Raks -= $PlanetRow['interceptor_misil'];

				$irak = $this->raketenangriff($Defender['defence_tech'], $Attacker['military_tech'], $Raks, $TargetDefensive, $Primary);

				ksort($irak, SORT_NUMERIC);
				$sql = '';

				foreach ($irak as $Element => $destroy)
				{
					if (empty($Element) || $destroy == 0)
						continue;

					$message .= _getText('tech', $Element) . " (" . $destroy . " уничтожено)<br>";

					if ($sql != '')
						$sql .= ', ';

					$sql .= $this->game->resource[$Element] . ' = ' . $this->game->resource[$Element] . ' - ' . $destroy . ' ';
				}

				if ($sql != '')
					$this->db->query("UPDATE game_planets SET " . $sql . " WHERE id = " . $PlanetRow['id']);
			}

			if (empty($message))
				$message = "Нет обороны для разрушения!";

			$this->game->sendMessage($this->_fleet['fleet_target_owner'], 0, $this->_fleet['fleet_start_time'], 3, 'Ракетная атака', $message);
		}
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		return;
	}

	private function raketenangriff ($TargetDefTech, $OwnerAttTech, $ipm, $TargetDefensive, $pri_target = 0)
	{
		unset($TargetDefensive[502]);

		$life_fac = $TargetDefTech / 10 + 1;
		$life_fac_a = $this->game->CombatCaps[503]['attack'] * ($OwnerAttTech / 10 + 1);

		$max_dam = $ipm * $life_fac_a;
		$i = 0;

		$ship_res = array();

		foreach ($TargetDefensive as $Element => $Count)
		{
			if ($i == 0)
				$target = $pri_target;
			elseif ($Element <= $pri_target)
				$target = $Element - 1;
			else
				$target = $Element;

			$Dam = $max_dam - ($this->game->pricelist[$target]['metal'] + $this->game->pricelist[$target]['crystal']) / 10 * $TargetDefensive[$target] * $life_fac;

			if ($Dam > 0)
			{
				$dest = $TargetDefensive[$target];
				$ship_res[$target] = $dest;
			}
			else
			{
				$dest = floor($max_dam / (($this->game->pricelist[$target]['metal'] + $this->game->pricelist[$target]['crystal']) / 10 * $life_fac));
				$ship_res[$target] = $dest;
			}
			$max_dam -= $dest * round(($this->game->pricelist[$target]['metal'] + $this->game->pricelist[$target]['crystal']) / 10 * $life_fac);
			$i++;
		}

		return $ship_res;
	}
}

?>