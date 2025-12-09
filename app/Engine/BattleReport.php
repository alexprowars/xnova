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

		$html = '<div class="report">';
		$bbc = '';

		$html .= sprintf('В %s произошёл бой между следующими флотами:', Game::datezone('d-m-Y H:i:s', $this->result_array['time']));
		$html .= '<div class="flex row gap-2 justify-center">';

		if (is_array($this->attackUsers)) {
			$checkName = [];

			foreach ($this->attackUsers as $info) {
				if (in_array($info['username'], $checkName)) {
					continue;
				}

				$html .= '<div><table class="table report_user">
							<tr><td class="c" colspan="3"><div class="text-center negative">' . $info['username'] . '</div></td></tr>
							<tr><th>Технология</th><th>Ур.</th><th>%</th></tr>
							<tr><th>Оружие</th><th>' . $info['tech']['military_tech'] . '</th><th>' . ($info['tech']['military_tech'] * 5) . '</th></tr>
							<tr><th>Щиты</th><th>' . $info['tech']['shield_tech'] . '</th><th>' . ($info['tech']['shield_tech'] * 5) . '</th></tr>
							<tr><th>Броня</th><th>' . $info['tech']['defence_tech'] . '</th><th>' . ($info['tech']['defence_tech'] * 5) . '</th></tr>
							<tr><th>Лазер</th><th>' . $info['tech']['laser_tech'] . '</th><th>' . ($info['tech']['laser_tech'] * 5) . '</th></tr>
							<tr><th>Ион</th><th>' . $info['tech']['ionic_tech'] . '</th><th>' . ($info['tech']['ionic_tech'] * 5) . '</th></tr>
							<tr><th>Плазма</th><th>' . $info['tech']['buster_tech'] . '</th><th>' . ($info['tech']['buster_tech'] * 5) . '</th></tr></table></div>';

				$checkName[] = $info['username'];
			}
		}

		if (is_array($this->defenseUsers)) {
			$checkName = [];

			foreach ($this->defenseUsers as $info) {
				if (in_array($info['username'], $checkName)) {
					continue;
				}

				$html .= '<div><table class="table report_user">
							<tr><td class="c" colspan="3"><div class="text-center positive">' . $info['username'] . '</div></td></tr>
							<tr><th>Технология</th><th>Ур.</th><th>%</th></tr>
							<tr><th>Оружие</th><th>' . $info['tech']['military_tech'] . '</th><th>' . ($info['tech']['military_tech'] * 5) . '</th></tr>
							<tr><th>Щиты</th><th>' . $info['tech']['shield_tech'] . '</th><th>' . ($info['tech']['shield_tech'] * 5) . '</th></tr>
							<tr><th>Броня</th><th>' . $info['tech']['defence_tech'] . '</th><th>' . ($info['tech']['defence_tech'] * 5) . '</th></tr>
							<tr><th>Лазер</th><th>' . $info['tech']['laser_tech'] . '</th><th>' . ($info['tech']['laser_tech'] * 5) . '</th></tr>
							<tr><th>Ион</th><th>' . $info['tech']['ionic_tech'] . '</th><th>' . ($info['tech']['ionic_tech'] * 5) . '</th></tr>
							<tr><th>Плазма</th><th>' . $info['tech']['buster_tech'] . '</th><th>' . ($info['tech']['buster_tech'] * 5) . '</th></tr></table></div>';

				$checkName[] = $info['username'];
			}
		}

		$html .= '</div>';

		foreach ($this->result_array['rw'] as $round => $data) {
			if ($data['attackA']['total'] > 0 && $data['defenseA']['total'] > 0) {
				$html .= '<div class="text-center">';
				$html .= sprintf('Атакующий флот делает %s выстрела(ов) с общей мощностью %s по защитнику. Щиты защитника поглощают %s мощности.<br>', Format::number($data['attackA']['total']), Format::number($data['attack']['total']), Format::number($data['defShield']));
				$html .= sprintf('Защитный флот делает %s выстрела(ов) с общей мощностью %s по атакующему. Щиты атакующего поглащают %s мощности.', Format::number($data['defenseA']['total']), Format::number($data['defense']['total']), Format::number($data['attackShield']));
				$html .= '</div>';
			}

			$attackers = $data['attackers'];
			$defenders = $data['defenders'];

			if (!count($attackers)) {
				$html .= '<div class="report_fleet">Атакующий флот уничтожен</div>';
			}

			foreach ($attackers as $fleet_id => $data2) {
				$user = $usersInfo[$fleet_id]['user_id'];

				$html .= '<div class="report_fleet">';
				$html .= '<div class="mb-2 negative">Атакующий ' . $this->attackUsers[$user]['username'] . ' [' . $usersInfo[$fleet_id]['galaxy'] . ':' . $usersInfo[$fleet_id]['system'] . ':' . $usersInfo[$fleet_id]['planet'] . ']</div>';
				$html .= '<table class="table">';

				if ($data['attackA'][$fleet_id] > 0) {
					$raport1 = '<tr><th>Тип</th>';
					$raport2 = '<tr><th>Кол-во</th>';
					$raport3 = '<tr><th>Атака</th>';
					$raport4 = '<tr><th>Корпус</th>';

					foreach ($data2 as $ship_id => $ship_count) {
						if ($ship_count > 0) {
							$raport1 .= '<th>' . __('main.tech.' . $ship_id) . '</th>';

							if ($round == 0) {
								$raport2 .= '<th>' . Format::number(ceil($ship_count)) . '</th>';
							} else {
								$raport2 .= '<th>' . Format::number(ceil($ship_count));

								if (ceil($this->result_array['rw'][$round - 1]['attackers'][$fleet_id][$ship_id]) - ceil($ship_count) > 0) {
									$raport2 .= ' <small><span style="color: red">-' . (ceil($this->result_array['rw'][$round - 1]['attackers'][$fleet_id][$ship_id]) - ceil($ship_count)) . '</span></small>';
								}

								$raport2 .= '</th>';
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

							$raport3 .= '<th>' . Format::number(round($fleetData['attack'] * $attTech)) . '</th>';
							$raport4 .= '<th>' . Format::number(round((Vars::getItemTotalPrice($ship_id) / 10) * (1 + $this->attackUsers[$user]['tech']['defence_tech'] * 0.05))) . '</th>';
						}
					}

					$raport1 .= '</tr>';
					$raport2 .= '</tr>';
					$raport3 .= '</tr>';
					$raport4 .= '</tr>';

					$html .= $raport1 . $raport2 . $raport3 . $raport4;
				} else {
					$html .= '<br>уничтожен';
				}

				$html .= '</table>';
				$html .= '</div>';
			}

			if (!count($defenders)) {
				$html .= '<div class="report_fleet">Защитный флот уничтожен</div>';
			}

			foreach ($defenders as $fleet_id => $data2) {
				$user = $usersInfo[$fleet_id]['user_id'];

				$html .= '<div class="report_fleet">';
				$html .= '<div class="mb-2 positive">Защитник ' . $this->defenseUsers[$user]['username'] . ' [' . $usersInfo[$fleet_id]['galaxy'] . ':' . $usersInfo[$fleet_id]['system'] . ':' . $usersInfo[$fleet_id]['planet'] . ']</div>';

				$html .= '<table class="table">';

				if ($data['defenseA'][$fleet_id] > 0) {
					$raport1 = '<tr><th>Тип</th>';
					$raport2 = '<tr><th>Кол-во</th>';
					$raport3 = '<tr><th>Атака</th>';
					$raport4 = '<tr><th>Корпус</th>';

					foreach ($data2 as $ship_id => $ship_count) {
						if ($ship_count > 0) {
							$raport1 .= '<th>' . __('main.tech.' . $ship_id) . '</th>';

							if ($round == 0) {
								$raport2 .= '<th>' . Format::number(ceil($ship_count)) . '</th>';
							} else {
								$raport2 .= '<th>' . Format::number(ceil($ship_count));

								if (ceil($this->result_array['rw'][$round - 1]['defenders'][$fleet_id][$ship_id]) - ceil($ship_count) > 0) {
									$raport2 .= ' <small><span style="color: red">-' . (ceil($this->result_array['rw'][$round - 1]['defenders'][$fleet_id][$ship_id]) - ceil($ship_count)) . '</span></small>';
								}

								$raport2 .= '</th>';
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

							$raport3 .= '<th>' . Format::number(round($fleetData['attack'] * $attTech)) . '</th>';
							$raport4 .= '<th>' . Format::number(round((Vars::getItemTotalPrice($ship_id) / 10) * (1 + $this->defenseUsers[$user]['tech']['defence_tech'] * 0.05))) . '</th>';
						}
					}

					$raport1 .= '</tr>';
					$raport2 .= '</tr>';
					$raport3 .= '</tr>';
					$raport4 .= '</tr>';

					$html .= $raport1 . $raport2 . $raport3 . $raport4;
				} else {
					$html .= '<br>уничтожен';
				}

				$html .= '</table>';
				$html .= '</div>';
			}
		}

		if ($this->result_array['won'] == 2) {
			$result1 = 'Обороняющийся выиграл битву!';
		} elseif ($this->result_array['won'] == 1) {
			$result1 = sprintf('Атакующий выиграл битву!<br>Он получает %s металла, %s кристалла и %s дейтерия', Format::number($this->steal_array['metal']), Format::number($this->steal_array['crystal']), Format::number($this->steal_array['deuterium']));
		} else {
			$result1 = 'Бой закончился ничьёй!';
		}

		$html .= '<div><table class="table report_result"><tr><td class="c">' . $result1 . '</td></tr>';

		$debirs_meta = ($this->result_array['debree']['att'][0] + $this->result_array['debree']['def'][0]);
		$debirs_crys = ($this->result_array['debree']['att'][1] + $this->result_array['debree']['def'][1]);

		$html .= '<tr><th>' . sprintf('Атакующий потерял %s единиц', Format::number($this->result_array['lost']['att'])) . '.</th></tr>';
		$html .= '<tr><th>' . sprintf('Обороняющийся потерял %s единиц', Format::number($this->result_array['lost']['def'])) . '</th></tr>';
		$html .= '<tr><td class="c">' . sprintf('Поле обломков: %s металла и %s кристалла.', Format::number($debirs_meta), Format::number($debirs_crys)) . '</td></tr>';

		$html .= '<tr><th>' . sprintf('Шанс появления луны составляет %s %%', $this->moon_int) . '<br>';
		$html .= $this->moon_string . '</th></tr>';

		$html .= '</table></div>';

		if (count($this->repair)) {
			foreach ($this->repair as $data2) {
				$html .= '<div class="report_fleet mb-2"><span class="neutral">Восстановленная оборона:</div>';
				$html .= '<table class="table">';

				$raport1 = '';
				$raport2 = '';

				foreach ($data2 as $ship_id => $ship_count) {
					if ($ship_count > 0) {
						$raport1 .= '<th>' . __('main.tech.' . $ship_id) . '</th>';
						$raport2 .= '<th>' . Format::number(ceil($ship_count)) . '</th>';
					}
				}
				$raport1 .= '</tr>';
				$raport2 .= '</tr>';
				$html .= $raport1 . $raport2;

				$html .= '</table>';
				$html .= '</div>';
			}
		}

		$html .= '<a href="' . $this->convertToSimLink([$this->result_array, $this->attackUsers, $this->defenseUsers]) . '" target="_blank">Симуляция</a>';
		$html .= '</div>';

		return ['html' => $html, 'bbc' => $bbc];
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

		return URL::to('/sim/report?r=' . implode('|', $att) . '|' . implode('|', $def));
	}
}
