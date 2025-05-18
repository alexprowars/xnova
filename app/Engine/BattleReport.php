<?php

namespace App\Engine;

use App\Format;
use App\Facades\Vars;
use Illuminate\Support\Facades\URL;

class BattleReport
{
	public function __construct(protected $result_array, protected $attackUsers, protected $defenseUsers, protected $steal_array, protected $moon_int = 0, protected $moon_string = '', protected $repair = [])
	{
	}

	public function report()
	{
		$usersInfo = [];

		foreach ($this->attackUsers as $userId => $u) {
			foreach ($u['fleet'] as $id => $f) {
				if (!is_numeric($id)) {
					continue;
				}

				$usersInfo[$id] = $f;
				$usersInfo[$id]['user_id'] = $userId;
			}
		}

		foreach ($this->defenseUsers as $userId => $u) {
			foreach ($u['fleet'] as $id => $f) {
				if (!is_numeric($id)) {
					continue;
				}

				$usersInfo[$id] = $f;
				$usersInfo[$id]['user_id'] = $userId;
			}
		}

		$html = "<div class='report'>";
		$bbc = "";

		$html .= "В " . Game::datezone("d-m-Y H:i:s", $this->result_array['time']) . " произошёл бой между следующими флотами:<div class='separator'></div><table align='center'><tr>";

		if (is_array($this->attackUsers)) {
			$checkName = [];

			foreach ($this->attackUsers as $info) {
				if (in_array($info['username'], $checkName)) {
					continue;
				}

				$html .= '<td><table class="report_user" align="center">
							<tr><td class="c" colspan="3"><span class="negative">' . $info['username'] . '</span></td></tr>
							<tr><th>Технология</th><th>Ур.</th><th>%</th></tr>
							<tr><th>Оружие</th><th>' . $info['tech']['military_tech'] . '</th><th>' . ($info['tech']['military_tech'] * 5) . '</th></tr>
							<tr><th>Щиты</th><th>' . $info['tech']['shield_tech'] . '</th><th>' . ($info['tech']['shield_tech'] * 5) . '</th></tr>
							<tr><th>Броня</th><th>' . $info['tech']['defence_tech'] . '</th><th>' . ($info['tech']['defence_tech'] * 5) . '</th></tr>
							<tr><th>Лазер</th><th>' . $info['tech']['laser_tech'] . '</th><th>' . ($info['tech']['laser_tech'] * 5) . '</th></tr>
							<tr><th>Ион</th><th>' . $info['tech']['ionic_tech'] . '</th><th>' . ($info['tech']['ionic_tech'] * 5) . '</th></tr>
							<tr><th>Плазма</th><th>' . $info['tech']['buster_tech'] . '</th><th>' . ($info['tech']['buster_tech'] * 5) . '</th></tr></table></td>';

				$checkName[] = $info['username'];
			}
		}

		if (is_array($this->defenseUsers)) {
			$checkName = [];

			foreach ($this->defenseUsers as $info) {
				if (in_array($info['username'], $checkName)) {
					continue;
				}

				$html .= '<td><table class="report_user" align="center">
							<tr><td class="c" colspan="3"><span class="positive">' . $info['username'] . '</span></td></tr>
							<tr><th>Технология</th><th>Ур.</th><th>%</th></tr>
							<tr><th>Оружие</th><th>' . $info['tech']['military_tech'] . '</th><th>' . ($info['tech']['military_tech'] * 5) . '</th></tr>
							<tr><th>Щиты</th><th>' . $info['tech']['shield_tech'] . '</th><th>' . ($info['tech']['shield_tech'] * 5) . '</th></tr>
							<tr><th>Броня</th><th>' . $info['tech']['defence_tech'] . '</th><th>' . ($info['tech']['defence_tech'] * 5) . '</th></tr>
							<tr><th>Лазер</th><th>' . $info['tech']['laser_tech'] . '</th><th>' . ($info['tech']['laser_tech'] * 5) . '</th></tr>
							<tr><th>Ион</th><th>' . $info['tech']['ionic_tech'] . '</th><th>' . ($info['tech']['ionic_tech'] * 5) . '</th></tr>
							<tr><th>Плазма</th><th>' . $info['tech']['buster_tech'] . '</th><th>' . ($info['tech']['buster_tech'] * 5) . '</th></tr></table></td>';

				$checkName[] = $info['username'];
			}
		}

		$html .= '</tr></table><br>';

		$round_no = 1;

		foreach ($this->result_array['rw'] as $round => $data) {
			if ($data['attackA']['total'] > 0 && $data['defenseA']['total'] > 0) {
				$html .= "<div class='separator'></div><center>Атакующий флот делает " . Format::number($data['attackA']['total']) . " выстрела(ов) с общей мощностью " . Format::number($data['attack']['total']) . " по защитнику. Щиты защитника поглощают " . Format::number($data['defShield']) . " мощности.<br />";
				$html .= "Защитный флот делает " . Format::number($data['defenseA']['total']) . " выстрела(ов) с общей мощностью " . Format::number($data['defense']['total']) . " по атакующему. Щиты атакующего поглащают " . Format::number($data['attackShield']) . " мощности.</center><div class='separator'></div>";
			}

			$attackers = $data['attackers'];
			$defenders = $data['defenders'];

			if (!count($attackers)) {
				$html .= '<div class="report_fleet"><div class="separator"></div><center>Атакующий флот уничтожен</center><div class="separator"></div></div>';
			}

			foreach ($attackers as $fleet_id => $data2) {
				$user = $usersInfo[$fleet_id]['user_id'];

				$html .= "<div class='report_fleet'>";
				$html .= "<span class='negative'>Атакующий " . $this->attackUsers[$user]['username'] . " [" . $usersInfo[$fleet_id]['galaxy'] . ":" . $usersInfo[$fleet_id]['system'] . ":" . $usersInfo[$fleet_id]['planet'] . "]</span><div class='separator'></div>";
				$html .= "<table border=1>";

				if ($data['attackA'][$fleet_id] > 0) {
					$raport1 = "<tr><th>Тип</th>";
					$raport2 = "<tr><th>Кол-во</th>";
					$raport3 = "<tr><th>Атака</th>";
					$raport4 = "<tr><th>Корпус</th>";

					foreach ($data2 as $ship_id => $ship_count) {
						if ($ship_count > 0) {
							$raport1 .= "<th>" . __('main.tech.' . $ship_id) . "</th>";

							if ($round == 0) {
								$raport2 .= "<th>" . Format::number(ceil($ship_count)) . "</th>";
							} else {
								$raport2 .= "<th>" . Format::number(ceil($ship_count));

								if (ceil($this->result_array['rw'][$round - 1]['attackers'][$fleet_id][$ship_id]) - ceil($ship_count) > 0) {
									$raport2 .= " <small><font color='red'>-" . (ceil($this->result_array['rw'][$round - 1]['attackers'][$fleet_id][$ship_id]) - ceil($ship_count)) . "</font></small>";
								}

								$raport2 .= "</th>";
							}

							$attTech = 1 + $this->attackUsers[$user]['tech']['military_tech'] * 0.05;

							$fleetData = Vars::getUnitData($ship_id);

							if ($fleetData['type_gun'] == 1) {
								$attTech += $this->attackUsers[$user]['tech']['laser_tech'] * 0.05;
							} elseif ($fleetData['type_gun'] == 2) {
								$attTech += $this->attackUsers[$user]['tech']['ionic_tech'] * 0.05;
							} elseif ($fleetData['type_gun'] == 3) {
								$attTech += $this->attackUsers[$user]['tech']['buster_tech'] * 0.05;
							}

							$raport3 .= "<th>" . Format::number(round($fleetData['attack'] * $attTech)) . "</th>";
							$raport4 .= "<th>" . Format::number(round((Vars::getItemTotalPrice($ship_id) / 10) * (1 + $this->attackUsers[$user]['tech']['defence_tech'] * 0.05))) . "</th>";
						}
					}

					$raport1 .= "</tr>";
					$raport2 .= "</tr>";
					$raport3 .= "</tr>";
					$raport4 .= "</tr>";

					$html .= $raport1 . $raport2 . $raport3 . $raport4;
				} else {
					$html .= "<br>уничтожен";
				}

				$html .= "</table>";
				$html .= "</div>";
			}

			$html .= '<div class="separator"></div>';

			if (!count($defenders)) {
				$html .= '<div class="report_fleet"><div class="separator"></div><center>Защитный флот уничтожен</center><div class="separator"></div></div>';
			}

			foreach ($defenders as $fleet_id => $data2) {
				$user = $usersInfo[$fleet_id]['user_id'];

				$html .= "<div class='report_fleet'>";
				$html .= "<span class='positive'>Защитник " . $this->defenseUsers[$user]['username'] . " [" . $usersInfo[$fleet_id]['galaxy'] . ":" . $usersInfo[$fleet_id]['system'] . ":" . $usersInfo[$fleet_id]['planet'] . "]</span><div class='separator'></div>";

				$html .= "<table border=1 align=\"center\">";

				if ($data['defenseA'][$fleet_id] > 0) {
					$raport1 = "<tr><th>Тип</th>";
					$raport2 = "<tr><th>Кол-во</th>";
					$raport3 = "<tr><th>Атака</th>";
					$raport4 = "<tr><th>Корпус</th>";

					foreach ($data2 as $ship_id => $ship_count) {
						if ($ship_count > 0) {
							$raport1 .= "<th>" . __('main.tech.' . $ship_id) . "</th>";

							if ($round == 0) {
								$raport2 .= "<th>" . Format::number(ceil($ship_count)) . "</th>";
							} else {
								$raport2 .= "<th>" . Format::number(ceil($ship_count));

								if (ceil($this->result_array['rw'][$round - 1]['defenders'][$fleet_id][$ship_id]) - ceil($ship_count) > 0) {
									$raport2 .= " <small><font color='red'>-" . (ceil($this->result_array['rw'][$round - 1]['defenders'][$fleet_id][$ship_id]) - ceil($ship_count)) . "</font></small>";
								}

								$raport2 .= "</th>";
							}

							$attTech = 1 + $this->defenseUsers[$user]['tech']['military_tech'] * 0.05;

							$fleetData = Vars::getUnitData($ship_id);

							if ($fleetData['type_gun'] == 1) {
								$attTech += $this->defenseUsers[$user]['tech']['laser_tech'] * 0.05;
							} elseif ($fleetData['type_gun'] == 2) {
								$attTech += $this->defenseUsers[$user]['tech']['ionic_tech'] * 0.05;
							} elseif ($fleetData['type_gun'] == 3) {
								$attTech += $this->defenseUsers[$user]['tech']['buster_tech'] * 0.05;
							}

							$raport3 .= "<th>" . Format::number(round($fleetData['attack'] * $attTech)) . "</th>";
							$raport4 .= "<th>" . Format::number(round((Vars::getItemTotalPrice($ship_id) / 10) * (1 + $this->defenseUsers[$user]['tech']['defence_tech'] * 0.05))) . "</th>";
						}
					}

					$raport1 .= "</tr>";
					$raport2 .= "</tr>";
					$raport3 .= "</tr>";
					$raport4 .= "</tr>";

					$html .= $raport1 . $raport2 . $raport3 . $raport4;
				} else {
					$html .= "<br>уничтожен";
				}

				$html .= "</table>";
				$html .= "</div>";
			}

			$round_no++;
		}

		if ($this->result_array['won'] == 2) {
			$result1 = "Обороняющийся выиграл битву!<br />";
		} elseif ($this->result_array['won'] == 1) {
			$result1 = "Атакующий выиграл битву!<br />";
			$result1 .= "Он получает " . Format::number($this->steal_array['metal']) . " металла, " . Format::number($this->steal_array['crystal']) . " кристалла и " . Format::number($this->steal_array['deuterium']) . " дейтерия<br />";
		} else {
			$result1 = "Бой закончился ничьёй!<br />";
		}

		$html .= "<br><br><table class='report_result'><tr><td class='c'>" . $result1 . "</td></tr>";

		$debirs_meta = ($this->result_array['debree']['att'][0] + $this->result_array['debree']['def'][0]);
		$debirs_crys = ($this->result_array['debree']['att'][1] + $this->result_array['debree']['def'][1]);

		$html .= "<tr><th>Атакующий потерял " . Format::number($this->result_array['lost']['att']) . " единиц.</th></tr>";
		$html .= "<tr><th>Обороняющийся потерял " . Format::number($this->result_array['lost']['def']) . " единиц.</th></tr>";
		$html .= "<tr><td class='c'>Поле обломков: " . Format::number($debirs_meta) . " металла и " . Format::number($debirs_crys) . " кристалла.</td></tr>";

		$html .= "<tr><th>Шанс появления луны составляет " . $this->moon_int . "%<br>";
		$html .= $this->moon_string . "</th></tr>";

		$html .= "</table><br><br>";

		if (count($this->repair)) {
			foreach ($this->repair as $data2) {
				$html .= "<div class='report_fleet'><span class='neutral'>Восстановленная оборона:</span><div class='separator'></div>";
				$html .= "<table border=1 align=\"center\">";

				$raport1 = "";
				$raport2 = "";

				foreach ($data2 as $ship_id => $ship_count) {
					if ($ship_count > 0) {
						$raport1 .= "<th>" . __('main.tech.' . $ship_id) . "</th>";
						$raport2 .= "<th>" . Format::number(ceil($ship_count)) . "</th>";
					}
				}
				$raport1 .= "</tr>";
				$raport2 .= "</tr>";
				$html .= $raport1 . $raport2;

				$html .= "</table>";
				$html .= "</div>";
			}
		}

		$html .= '<br><br>';
		$html .= '<a href="' . $this->convertToSimLink(array($this->result_array, $this->attackUsers, $this->defenseUsers)) . '" target="_blank">Симуляция</a>';

		$html .= "</div>";

		return array('html' => $html, 'bbc' => $bbc);
	}

	public function convertToSimLink($result)
	{
		$usersInfo = [];

		foreach ($result[1] as $userId => $u) {
			foreach ($u['fleet'] as $id => $f) {
				if (!is_numeric($id)) {
					continue;
				}

				$usersInfo[$id] = $userId;
			}
		}

		foreach ($result[2] as $userId => $u) {
			foreach ($u['fleet'] as $id => $f) {
				if (!is_numeric($id)) {
					continue;
				}

				$usersInfo[$id] = $userId;
			}
		}

		$att = [];

		$j = 0;

		foreach ($result[0]['rw'][0]['attackers'] as $i => $a) {
			$j++;

			$t = [];

			foreach ($result[1][$usersInfo[$i]]['flvl'] as $j => $l) {
				if ($j < 200) {
					$t[] = $j . ',' . $l;
				}
			}

			foreach ($a as $s => $c) {
				$t[] = $s . ',' . $c;
			}

			$att[] = implode(';', $t);

			if ($j == 10) {
				break;
			}
		}

		for ($i = count($att); $i < 10; $i++) {
			$att[] = '';
		}

		$def = [];

		$j = 0;

		foreach ($result[0]['rw'][0]['defenders'] as $i => $a) {
			$j++;

			$t = [];

			foreach ($result[2][$usersInfo[$i]]['flvl'] as $j => $l) {
				if ($j < 200) {
					$t[] = $j . ',' . $l;
				}
			}

			foreach ($a as $s => $c) {
				$t[] = $s . ',' . $c;
			}

			$def[] = implode(';', $t);

			if ($j == 10) {
				break;
			}
		}

		for ($i = count($def); $i < 10; $i++) {
			$def[] = '';
		}

		return URL::to('/xnsim/report/?r=' . implode('|', $att) . '|' . implode('|', $def));
	}
}
