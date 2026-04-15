<?php

namespace App\Engine\Battle;

use App\Engine\Game;
use App\Facades\Vars;
use App\Format;
use Illuminate\Support\Facades\URL;

class BattleReport
{
	public function __construct(protected array $resultData)
	{
	}

	public function report(): string
	{
		$usersInfo = [];

		foreach ($this->resultData['attackers'] as $userId => $u) {
			foreach ($u['fleet'] as $f) {
				if (!is_numeric($f['id'])) {
					continue;
				}

				$usersInfo[$f['id']] = $f;
				$usersInfo[$f['id']]['user_id'] = $userId;
			}
		}

		foreach ($this->resultData['defenders'] as $userId => $u) {
			foreach ($u['fleet'] as $f) {
				if (!is_numeric($f['id'])) {
					continue;
				}

				$usersInfo[$f['id']] = $f;
				$usersInfo[$f['id']]['user_id'] = $userId;
			}
		}

		$html = '<div class="report">';

		$html .= sprintf('В %s произошёл бой между следующими флотами:', Game::datezone('d.m.Y H:i:s', $this->resultData['date']));
		$html .= '<div class="flex row gap-2 justify-center">';

		$checkName = [];

		foreach ($this->resultData['attackers'] as $info) {
			if (in_array($info['name'], $checkName)) {
				continue;
			}

			$html .= '<div><table class="table report_user">
						<tr><td class="c" colspan="3"><div class="text-center negative">' . $info['name'] . '</div></td></tr>
						<tr><th>Технология</th><th>Ур.</th><th>%</th></tr>
						<tr><th>Оружие</th><th>' . $info['tech']['military_tech'] . '</th><th>' . ($info['tech']['military_tech'] * 5) . '</th></tr>
						<tr><th>Щиты</th><th>' . $info['tech']['shield_tech'] . '</th><th>' . ($info['tech']['shield_tech'] * 5) . '</th></tr>
						<tr><th>Броня</th><th>' . $info['tech']['defence_tech'] . '</th><th>' . ($info['tech']['defence_tech'] * 5) . '</th></tr>
						<tr><th>Лазер</th><th>' . $info['tech']['laser_tech'] . '</th><th>' . ($info['tech']['laser_tech'] * 5) . '</th></tr>
						<tr><th>Ион</th><th>' . $info['tech']['ionic_tech'] . '</th><th>' . ($info['tech']['ionic_tech'] * 5) . '</th></tr>
						<tr><th>Плазма</th><th>' . $info['tech']['buster_tech'] . '</th><th>' . ($info['tech']['buster_tech'] * 5) . '</th></tr></table></div>';

			$checkName[] = $info['name'];
		}

		$checkName = [];

		foreach ($this->resultData['defenders'] as $info) {
			if (in_array($info['name'], $checkName)) {
				continue;
			}

			$html .= '<div><table class="table report_user">
						<tr><td class="c" colspan="3"><div class="text-center positive">' . $info['name'] . '</div></td></tr>
						<tr><th>Технология</th><th>Ур.</th><th>%</th></tr>
						<tr><th>Оружие</th><th>' . $info['tech']['military_tech'] . '</th><th>' . ($info['tech']['military_tech'] * 5) . '</th></tr>
						<tr><th>Щиты</th><th>' . $info['tech']['shield_tech'] . '</th><th>' . ($info['tech']['shield_tech'] * 5) . '</th></tr>
						<tr><th>Броня</th><th>' . $info['tech']['defence_tech'] . '</th><th>' . ($info['tech']['defence_tech'] * 5) . '</th></tr>
						<tr><th>Лазер</th><th>' . $info['tech']['laser_tech'] . '</th><th>' . ($info['tech']['laser_tech'] * 5) . '</th></tr>
						<tr><th>Ион</th><th>' . $info['tech']['ionic_tech'] . '</th><th>' . ($info['tech']['ionic_tech'] * 5) . '</th></tr>
						<tr><th>Плазма</th><th>' . $info['tech']['buster_tech'] . '</th><th>' . ($info['tech']['buster_tech'] * 5) . '</th></tr></table></div>';

			$checkName[] = $info['name'];
		}

		$html .= '</div>';

		$position = null;

		foreach ($this->resultData['rounds'] as $round => $data) {
			if ($data['hits_attacker'] > 0 && $data['hits_defender'] > 0) {
				$html .= '<div class="text-center">';
				$html .= sprintf('Атакующий флот делает %s выстрела(ов) с общей мощностью %s по защитнику. Щиты защитника поглощают %s мощности.<br>', Format::number($data['hits_attacker']), Format::number($data['full_strength_attacker']), Format::number($data['absorbed_damage_defender']));
				$html .= sprintf('Защитный флот делает %s выстрела(ов) с общей мощностью %s по атакующему. Щиты атакующего поглащают %s мощности.', Format::number($data['hits_defender']), Format::number($data['full_strength_defender']), Format::number($data['absorbed_damage_attacker']));
				$html .= '</div>';
			}

			$attackers = $data['attackers'];
			$defenders = $data['defenders'];

			if (!count($attackers)) {
				$html .= '<div class="report_fleet">Атакующий флот уничтожен</div>';
			}

			$html .= '<div class="flex gap-2">';

			foreach ($attackers as $fleet_id => $data2) {
				$user = $usersInfo[$fleet_id]['user_id'];

				if ($position === null) {
					$position = [
						'galaxy' => $usersInfo[$fleet_id]['galaxy'],
						'system' => $usersInfo[$fleet_id]['system'],
						'planet' => $usersInfo[$fleet_id]['planet'],
					];
				}

				$html .= '<div class="report_fleet">';
				$html .= '<div class="mb-2 negative">Атакующий ' . $this->resultData['attackers'][$user]['name'] . ' [' . $usersInfo[$fleet_id]['galaxy'] . ':' . $usersInfo[$fleet_id]['system'] . ':' . $usersInfo[$fleet_id]['planet'] . ']</div>';
				$html .= '<table class="table">';

				if (array_sum($data2) > 0) {
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

								if (ceil($this->resultData['rounds'][$round - 1]['attackers'][$fleet_id][$ship_id]) - ceil($ship_count) > 0) {
									$raport2 .= ' <small><span style="color: red">-' . (ceil($this->resultData['rounds'][$round - 1]['attackers'][$fleet_id][$ship_id]) - ceil($ship_count)) . '</span></small>';
								}

								$raport2 .= '</th>';
							}

							$attTech = 1 + $this->resultData['attackers'][$user]['tech']['military_tech'] * 0.05;

							$fleetData = Vars::getUnitData($ship_id);

							if ($fleetData['type_gun'] == 1) {
								$attTech += $this->resultData['attackers'][$user]['tech']['laser_tech'] * 0.05;
							} elseif ($fleetData['type_gun'] == 2) {
								$attTech += $this->resultData['attackers'][$user]['tech']['ionic_tech'] * 0.05;
							} elseif ($fleetData['type_gun'] == 3) {
								$attTech += $this->resultData['attackers'][$user]['tech']['buster_tech'] * 0.05;
							}

							$raport3 .= '<th>' . Format::number(round($fleetData['attack'] * $attTech)) . '</th>';
							$raport4 .= '<th>' . Format::number(round((Vars::getItemTotalPrice($ship_id) / 10) * (1 + $this->resultData['attackers'][$user]['tech']['defence_tech'] * 0.05))) . '</th>';
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

			$html .= '</div>';

			if (!count($defenders)) {
				$html .= '<div class="report_fleet">Защитный флот уничтожен</div>';
			}

			$html .= '<div class="flex gap-2">';

			foreach ($defenders as $fleet_id => $data2) {
				$user = $usersInfo[$fleet_id]['user_id'];

				$html .= '<div class="report_fleet">';
				$html .= '<div class="mb-2 positive">Защитник ' . $this->resultData['defenders'][$user]['name'] . ' [' . $usersInfo[$fleet_id]['galaxy'] . ':' . $usersInfo[$fleet_id]['system'] . ':' . $usersInfo[$fleet_id]['planet'] . ']</div>';

				$html .= '<table class="table">';

				if (array_sum($data2) > 0) {
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

								if (ceil($this->resultData['rounds'][$round - 1]['defenders'][$fleet_id][$ship_id]) - ceil($ship_count) > 0) {
									$raport2 .= ' <small><span style="color: red">-' . (ceil($this->resultData['rounds'][$round - 1]['defenders'][$fleet_id][$ship_id]) - ceil($ship_count)) . '</span></small>';
								}

								$raport2 .= '</th>';
							}

							$attTech = 1 + $this->resultData['defenders'][$user]['tech']['military_tech'] * 0.05;

							$fleetData = Vars::getUnitData($ship_id);

							if ($fleetData['type_gun'] == 1) {
								$attTech += $this->resultData['defenders'][$user]['tech']['laser_tech'] * 0.05;
							} elseif ($fleetData['type_gun'] == 2) {
								$attTech += $this->resultData['defenders'][$user]['tech']['ionic_tech'] * 0.05;
							} elseif ($fleetData['type_gun'] == 3) {
								$attTech += $this->resultData['defenders'][$user]['tech']['buster_tech'] * 0.05;
							}

							$raport3 .= '<th>' . Format::number(round($fleetData['attack'] * $attTech)) . '</th>';
							$raport4 .= '<th>' . Format::number(round((Vars::getItemTotalPrice($ship_id) / 10) * (1 + $this->resultData['defenders'][$user]['tech']['defence_tech'] * 0.05))) . '</th>';
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

			$html .= '</div>';
		}

		if ($this->resultData['won'] == 2) {
			$result1 = 'Обороняющийся выиграл битву!';
		} elseif ($this->resultData['won'] == 1) {
			$result1 = 'Атакующий выиграл битву!';

			if (!empty($this->resultData['steal'])) {
				$result1 .= sprintf('<br>Он получает %s металла, %s кристалла и %s дейтерия', Format::number($this->resultData['steal']['metal']), Format::number($this->resultData['steal']['crystal']), Format::number($this->resultData['steal']['deuterium']));
			}
		} else {
			$result1 = 'Бой закончился ничьёй!';
		}

		$html .= '<div><table class="table report_result text-center"><tr><td class="c">' . $result1 . '</td></tr>';

		$debirs_meta = $this->resultData['debris']['metal'];
		$debirs_crys = $this->resultData['debris']['crystal'];

		$html .= '<tr><th>' . sprintf('Атакующий потерял %s единиц', Format::number($this->resultData['lost']['attackers'])) . '.</th></tr>';
		$html .= '<tr><th>' . sprintf('Обороняющийся потерял %s единиц', Format::number($this->resultData['lost']['defenders'])) . '</th></tr>';

		if ($debirs_meta > 0 || $debirs_crys > 0) {
			$html .= '<tr><td class="c">' . sprintf('Поле обломков: %s металла и %s кристалла.', Format::number($debirs_meta), Format::number($debirs_crys)) . '</td></tr>';
		}

		$html .= '<tr><th>' . sprintf('Шанс появления луны составляет %s %%', $this->resultData['moon_probability'] ?? 0) . '<br>';

		if (!empty($this->resultData['moon'])) {
			if ($this->resultData['moon'] == 1) {
				$html .= __('fleet_engine.sys_moonbuilt', [
					'galaxy' => $position['galaxy'] ?? '?',
					'system' => $position['system'] ?? '?',
					'planet' => $position['planet'] ?? '?',
				]);
			} else {
				$html .= 'Предпринята попытка образования луны, но данные координаты уже заняты другой луной';
			}
		}

		$html .= '</th></tr>';
		$html .= '</table></div>';

		if (!empty($this->resultData['repair'])) {
			foreach ($this->resultData['repair'] as $data2) {
				$html .= '<div class="report_fleet mb-2"><span class="neutral">Восстановленная оборона:</div>';
				$html .= '<div class="report_fleet mb-2"><table class="table">';

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
				$html .= '</div></div>';
			}
		}

		$html .= '<div class="text-center"><a href="' . $this->convertToSimLink($this->resultData, $this->resultData['attackers'], $this->resultData['defenders']) . '" target="_blank">Симуляция</a></div>';
		$html .= '</div>';

		return $html;
	}

	protected function convertToSimLink(array $resultData, array $attackUsers, array $defenseUsers): string
	{
		$usersInfo = [];

		foreach ($attackUsers as $userId => $u) {
			foreach ($u['fleet'] as $f) {
				if (!is_numeric($f['id'])) {
					continue;
				}

				$usersInfo[$f['id']] = $userId;
			}
		}

		foreach ($defenseUsers as $userId => $u) {
			foreach ($u['fleet'] as $f) {
				if (!is_numeric($f['id'])) {
					continue;
				}

				$usersInfo[$f['id']] = $userId;
			}
		}

		$att = [];

		$j = 0;

		foreach ($resultData['rounds'][0]['attackers'] as $i => $a) {
			$j++;

			$t = [];

			foreach ($attackUsers[$usersInfo[$i]]['units'] as $j => $l) {
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

		foreach ($resultData['rounds'][0]['defenders'] as $i => $a) {
			$j++;

			$t = [];

			foreach ($defenseUsers[$usersInfo[$i]]['units'] as $j => $l) {
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
