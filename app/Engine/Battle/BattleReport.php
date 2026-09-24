<?php

namespace App\Engine\Battle;

use App\Engine\Game;
use App\Engine\Objects\DefenceObject;
use App\Engine\Objects\ObjectsFactory;
use App\Format;
use Illuminate\Support\Facades\URL;

class BattleReport
{
	public function __construct(protected array $resultData)
	{
	}

	public function report(bool $showSimulationLink = true): string
	{
		$usersInfo = [];
		$position = null;

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

				$position ??= $f;
			}
		}

		$html = '<div class="report">';

		$html .= __('fleet_engine.battle.intro', ['date' => Game::datezone('d.m.Y H:i:s', $this->resultData['date'])]);
		$html .= '<div class="flex row gap-2 justify-center">';

		$checkName = [];

		foreach ($this->resultData['attackers'] as $info) {
			if (in_array($info['name'], $checkName)) {
				continue;
			}

			$html .= '<div><table class="table report_user">
						<tr><td class="c" colspan="3"><div class="text-center negative">' . $info['name'] . '</div></td></tr>
						<tr><th>' . __('fleet_engine.battle.technology') . '</th><th>' . __('fleet_engine.battle.level') . '</th><th>%</th></tr>
						<tr><th>' . __('fleet_engine.battle.weapon') . '</th><th>' . $info['tech']['military_tech'] . '</th><th>' . ($info['tech']['military_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.shield') . '</th><th>' . $info['tech']['shield_tech'] . '</th><th>' . ($info['tech']['shield_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.armor') . '</th><th>' . $info['tech']['defence_tech'] . '</th><th>' . ($info['tech']['defence_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.laser') . '</th><th>' . $info['tech']['laser_tech'] . '</th><th>' . ($info['tech']['laser_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.ion') . '</th><th>' . $info['tech']['ionic_tech'] . '</th><th>' . ($info['tech']['ionic_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.plasma') . '</th><th>' . $info['tech']['buster_tech'] . '</th><th>' . ($info['tech']['buster_tech'] * 5) . '</th></tr></table></div>';

			$checkName[] = $info['name'];
		}

		$checkName = [];

		foreach ($this->resultData['defenders'] as $info) {
			if (in_array($info['name'], $checkName)) {
				continue;
			}

			$html .= '<div><table class="table report_user">
						<tr><td class="c" colspan="3"><div class="text-center positive">' . $info['name'] . '</div></td></tr>
						<tr><th>' . __('fleet_engine.battle.technology') . '</th><th>' . __('fleet_engine.battle.level') . '</th><th>%</th></tr>
						<tr><th>' . __('fleet_engine.battle.weapon') . '</th><th>' . $info['tech']['military_tech'] . '</th><th>' . ($info['tech']['military_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.shield') . '</th><th>' . $info['tech']['shield_tech'] . '</th><th>' . ($info['tech']['shield_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.armor') . '</th><th>' . $info['tech']['defence_tech'] . '</th><th>' . ($info['tech']['defence_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.laser') . '</th><th>' . $info['tech']['laser_tech'] . '</th><th>' . ($info['tech']['laser_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.ion') . '</th><th>' . $info['tech']['ionic_tech'] . '</th><th>' . ($info['tech']['ionic_tech'] * 5) . '</th></tr>
						<tr><th>' . __('fleet_engine.battle.plasma') . '</th><th>' . $info['tech']['buster_tech'] . '</th><th>' . ($info['tech']['buster_tech'] * 5) . '</th></tr></table></div>';

			$checkName[] = $info['name'];
		}

		$html .= '</div>';

		foreach ($this->resultData['rounds'] as $round => $data) {
			if ($data['hits_attacker'] > 0 && $data['hits_defender'] > 0) {
				$html .= '<div class="text-center">';
				$html .= __('fleet_engine.battle.attacker_fire', ['hits' => Format::number($data['hits_attacker']), 'strength' => Format::number($data['full_strength_attacker']), 'absorbed' => Format::number($data['absorbed_damage_defender'])]);
				$html .= __('fleet_engine.battle.defender_fire', ['hits' => Format::number($data['hits_defender']), 'strength' => Format::number($data['full_strength_defender']), 'absorbed' => Format::number($data['absorbed_damage_attacker'])]);
				$html .= '</div>';
			}

			$attackers = $data['attackers'];
			$defenders = $data['defenders'];

			if (!count($attackers)) {
				$html .= '<div class="report_fleet">' . __('fleet_engine.battle.attacker_fleet_destroyed') . '</div>';
			}

			$html .= '<div class="flex gap-2">';

			foreach ($attackers as $fleet_id => $data2) {
				$user = $usersInfo[$fleet_id]['user_id'];

				$html .= '<div class="report_fleet">';
				$html .= '<div class="mb-2 negative">' . __('fleet_engine.battle.attacker') . ' ' . $this->resultData['attackers'][$user]['name'] . ' [' . $usersInfo[$fleet_id]['galaxy'] . ':' . $usersInfo[$fleet_id]['system'] . ':' . $usersInfo[$fleet_id]['planet'] . ']</div>';
				$html .= '<table class="table">';

				if (array_sum($data2) > 0) {
					$raport1 = '<tr><th>' . __('fleet_engine.battle.type') . '</th>';
					$raport2 = '<tr><th>' . __('fleet_engine.battle.count') . '</th>';
					$raport3 = '<tr><th>' . __('fleet_engine.battle.attack') . '</th>';
					$raport4 = '<tr><th>' . __('fleet_engine.battle.hull') . '</th>';

					foreach ($data2 as $ship_id => $ship_count) {
						if ($ship_count <= 0) {
							continue;
						}

						$fleetObject = ObjectsFactory::get($ship_id);

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

						if (!($fleetObject instanceof DefenceObject)) {
							continue;
						}

						$attTech = 1 + $this->resultData['attackers'][$user]['tech']['military_tech'] * 0.05;

						if ($fleetObject->getWeaponType() == 1) {
							$attTech += $this->resultData['attackers'][$user]['tech']['laser_tech'] * 0.05;
						} elseif ($fleetObject->getWeaponType() == 2) {
							$attTech += $this->resultData['attackers'][$user]['tech']['ionic_tech'] * 0.05;
						} elseif ($fleetObject->getWeaponType() == 3) {
							$attTech += $this->resultData['attackers'][$user]['tech']['buster_tech'] * 0.05;
						}

						$raport3 .= '<th>' . Format::number(round($fleetObject->getAttack() * $attTech)) . '</th>';
						$raport4 .= '<th>' . Format::number(round($fleetObject->getArmor() * (1 + $this->resultData['attackers'][$user]['tech']['defence_tech'] * 0.05))) . '</th>';
					}

					$raport1 .= '</tr>';
					$raport2 .= '</tr>';
					$raport3 .= '</tr>';
					$raport4 .= '</tr>';

					$html .= $raport1 . $raport2 . $raport3 . $raport4;
				} else {
					$html .= '<br>' . __('fleet_engine.battle.destroyed');
				}

				$html .= '</table>';
				$html .= '</div>';
			}

			$html .= '</div>';

			if (!count($defenders)) {
				$html .= '<div class="report_fleet">' . __('fleet_engine.battle.defender_fleet_destroyed') . '</div>';
			}

			$html .= '<div class="flex gap-2">';

			foreach ($defenders as $fleet_id => $data2) {
				$user = $usersInfo[$fleet_id]['user_id'];

				$html .= '<div class="report_fleet">';
				$html .= '<div class="mb-2 positive">' . __('fleet_engine.battle.defender') . ' ' . $this->resultData['defenders'][$user]['name'] . ' [' . $usersInfo[$fleet_id]['galaxy'] . ':' . $usersInfo[$fleet_id]['system'] . ':' . $usersInfo[$fleet_id]['planet'] . ']</div>';

				$html .= '<table class="table">';

				if (array_sum($data2) > 0) {
					$raport1 = '<tr><th>' . __('fleet_engine.battle.type') . '</th>';
					$raport2 = '<tr><th>' . __('fleet_engine.battle.count') . '</th>';
					$raport3 = '<tr><th>' . __('fleet_engine.battle.attack') . '</th>';
					$raport4 = '<tr><th>' . __('fleet_engine.battle.hull') . '</th>';

					foreach ($data2 as $ship_id => $ship_count) {
						if ($ship_count <= 0) {
							continue;
						}

						$fleetObject = ObjectsFactory::get($ship_id);

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

						if (!($fleetObject instanceof DefenceObject)) {
							continue;
						}

						$attTech = 1 + $this->resultData['defenders'][$user]['tech']['military_tech'] * 0.05;

						if ($fleetObject->getWeaponType() == 1) {
							$attTech += $this->resultData['defenders'][$user]['tech']['laser_tech'] * 0.05;
						} elseif ($fleetObject->getWeaponType() == 2) {
							$attTech += $this->resultData['defenders'][$user]['tech']['ionic_tech'] * 0.05;
						} elseif ($fleetObject->getWeaponType() == 3) {
							$attTech += $this->resultData['defenders'][$user]['tech']['buster_tech'] * 0.05;
						}

						$raport3 .= '<th>' . Format::number(round($fleetObject->getAttack() * $attTech)) . '</th>';
						$raport4 .= '<th>' . Format::number(round($fleetObject->getArmor() * (1 + $this->resultData['defenders'][$user]['tech']['defence_tech'] * 0.05))) . '</th>';
					}

					$raport1 .= '</tr>';
					$raport2 .= '</tr>';
					$raport3 .= '</tr>';
					$raport4 .= '</tr>';

					$html .= $raport1 . $raport2 . $raport3 . $raport4;
				} else {
					$html .= '<br>' . __('fleet_engine.battle.destroyed');
				}

				$html .= '</table>';
				$html .= '</div>';
			}

			$html .= '</div>';
		}

		if ($this->resultData['won'] == 2) {
			$result1 = __('fleet_engine.battle.defender_won');
		} elseif ($this->resultData['won'] == 1) {
			$result1 = __('fleet_engine.battle.attacker_won');

			if (!empty($this->resultData['steal'])) {
				$result1 .= '<br>' . __('fleet_engine.battle.stolen', ['metal' => Format::number($this->resultData['steal']['metal']), 'crystal' => Format::number($this->resultData['steal']['crystal']), 'deuterium' => Format::number($this->resultData['steal']['deuterium'])]);
			}
		} else {
			$result1 = __('fleet_engine.battle.draw');
		}

		$html .= '<div><table class="table report_result text-center"><tr><td class="c">' . $result1 . '</td></tr>';

		$debirs_meta = $this->resultData['debris']['metal'];
		$debirs_crys = $this->resultData['debris']['crystal'];

		$html .= '<tr><th>' . __('fleet_engine.battle.attacker_lost_units', ['count' => Format::number($this->resultData['lost']['attackers'])]) . '</th></tr>';
		$html .= '<tr><th>' . __('fleet_engine.battle.defender_lost_units', ['count' => Format::number($this->resultData['lost']['defenders'])]) . '</th></tr>';

		if ($debirs_meta > 0 || $debirs_crys > 0) {
			$html .= '<tr><td class="c">' . __('fleet_engine.battle.debris_field', ['metal' => Format::number($debirs_meta), 'crystal' => Format::number($debirs_crys)]) . '</td></tr>';
		}

		$html .= '<tr><th>' . __('fleet_engine.battle.moon_chance', ['chance' => $this->resultData['moon_probability'] ?? 0]) . '<br>';

		if (!empty($this->resultData['moon'])) {
			if ($this->resultData['moon'] == 1) {
				$html .= __('fleet_engine.battle.moon_created', [
					'galaxy' => $position['galaxy'] ?? '?',
					'system' => $position['system'] ?? '?',
					'planet' => $position['planet'] ?? '?',
				]);
			} else {
				$html .= __('fleet_engine.battle.moon_occupied');
			}
		}

		$html .= '</th></tr>';
		$html .= '</table></div>';

		if (!empty($this->resultData['repair'])) {
			foreach ($this->resultData['repair'] as $data2) {
				$repairedUnits = [];

				foreach ($data2 as $ship_id => $ship_count) {
					if ($ship_count > 0) {
						$repairedUnits[] = '<div class="report-repair-unit"><img src="/assets/images/elements/' . $ship_id . '.webp" alt="" width="36" height="36" loading="lazy"><span>' . __('main.tech.' . $ship_id) . '</span><strong>' . Format::number(ceil($ship_count)) . '</strong></div>';
					}
				}

				if (empty($repairedUnits)) {
					continue;
				}

				$html .= '<section class="report-repair">';
				$html .= '<div class="report-repair-heading"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.2 8.5 8 11 4.8-2.5 8-6 8-11V5l-8-3Z"/><path d="m8 12 2.5 2.5L16 9"/></svg><h2>' . __('fleet_engine.battle.repaired_defense') . '</h2></div>';
				$html .= '<div class="report-repair-units">' . implode('', $repairedUnits) . '</div>';
				$html .= '</section>';
			}
		}

		if ($showSimulationLink) {
			$html .= '<div class="report-actions"><a class="button report-simulation-link" href="' . $this->convertToSimLink($this->resultData, $this->resultData['attackers'], $this->resultData['defenders']) . '" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m7 4.5 8 5.5-8 5.5v-11Z"/></svg><span>' . __('fleet_engine.battle.simulation') . '</span></a></div>';
		}

		$html .= '</div>';

		return $html;
	}

	protected function convertToSimLink(array $resultData, array $attackUsers, array $defenseUsers): string
	{
		$usersInfo = [];

		foreach ([$attackUsers, $defenseUsers] as $group) {
			foreach ($group as $userId => $u) {
				foreach ($u['fleet'] as $f) {
					if (!is_numeric($f['id'])) {
						continue;
					}

					$usersInfo[$f['id']] = $userId;
				}
			}
		}

		$processSide = static function (array $sideData, array $users, array $usersInfo): array {
			$formatted = [];
			$count = 0;

			foreach ($sideData as $id => $data) {
				$userId = $usersInfo[$id] ?? null;
				if ($userId === null || !isset($users[$userId])) {
					continue;
				}

				$parts = [];

				foreach ($users[$userId]['units'] as $unitId => $unitCount) {
					if ($unitId < 200) {
						$parts[] = $unitId . ',' . $unitCount;
					}
				}

				foreach ($data as $shipId => $shipCount) {
					$parts[] = $shipId . ',' . $shipCount;
				}

				$formatted[] = implode(';', $parts);

				if (++$count === 10) {
					break;
				}
			}

			return array_pad($formatted, 10, '');
		};

		$att = $processSide($resultData['rounds'][0]['attackers'], $attackUsers, $usersInfo);
		$def = $processSide($resultData['rounds'][0]['defenders'], $defenseUsers, $usersInfo);

		return URL::to('/sim/report?r=' . implode('|', $att) . '|' . implode('|', $def));
	}
}
