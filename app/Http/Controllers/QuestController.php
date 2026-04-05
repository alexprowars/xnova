<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Format;

class QuestController extends Controller
{
	public function index()
	{
		$quests = require resource_path('engine/quests.php');

		$result = [];

		$userQuests = [];

		foreach ($this->user->quests as $quest) {
			$userQuests[$quest->quest_id] = $quest->toArray();
		}

		$result['items'] = [];
		$result['quests'] = $userQuests;

		foreach ($quests as $questId => $quest) {
			$available = true;

			if (isset($quest['required'])) {
				foreach ($quest['required'] as $key => $req) {
					if ($key == 'quest' && (!isset($userQuests[$req]) || ($userQuests[$req]['finish'] == 0))) {
						$available = false;
					}

					if ($key == 'level_minier' && $this->user->lvl_minier < $req) {
						$available = false;
					}

					if ($key == 'level_raid' && $this->user->lvl_raid < $req) {
						$available = false;
					}
				}
			}

			$result['items'][] = [
				'id' => $questId,
				'title' => __('quests.' . $questId . '.title'),
				'finish' => isset($userQuests[$questId]) && $userQuests[$questId]['finish'] == 1,
				'required' => $quest['required'],
				'available' => $available,
			];
		}

		return $result;
	}

	public function info(int $id)
	{
		if ($id <= 0) {
			throw new PageException('Не выбрано задание', '/quests');
		}

		$quest = require resource_path('engine/quests.php');

		if (!isset($quest[$id])) {
			throw new PageException('Задание не существует', '/quests');
		}

		$result = [
			'id' => $id,
			'title' => __('quests.' . $id . '.title'),
			'description' => __('quests.' . $id . '.description'),
			'solution' => __('quests.' . $id . '.solution'),
			'task' => [],
			'rewd' => [],
		];

		$qInfo = $this->user->quests()
			->where('quest_id', $id)
			->first();

		if (!$qInfo) {
			$qInfo = $this->user->quests()->create([
				'quest_id' 	=> $id,
				'finish' 	=> false,
				'stage' 	=> 0,
			]);
		}

		$errors = 0;
		$checks = $qInfo->checkFinished($this->user, $this->planet);

		foreach ($quest[$id]['task'] as $taskKey => $taskVal) {
			$check = $checks[$taskKey] ?? false;

			if ($taskKey == 'build') {
				foreach ($taskVal as $element => $level) {
					$type = Vars::getItemType($element);

					if ($type == ItemType::TECH) {
						$chk = $this->user->getTechLevel($element) >= $level;
					} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
						$chk = $this->planet->getLevel($element) >= $level;
					} else {
						$chk = $this->planet->getLevel($element) >= $level;
					}

					if ($type == ItemType::TECH) {
						$result['task'][] = ['Исследовать <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня', $chk];
					} elseif ($type == ItemType::FLEET) {
						$result['task'][] = ['Построить ' . $level . ' ед. флота типа <b>' . __('main.tech.' . $element) . '</b>', $chk];
					} elseif ($type == ItemType::DEFENSE) {
						$result['task'][] = ['Построить ' . $level . ' ед. обороны типа <b>' . __('main.tech.' . $element) . '</b>', $chk];
					} else {
						$result['task'][] = ['Построить <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня', $chk];
					}
				}
			}

			if ($taskKey == '!planet_name') {
				$result['task'][] = ['Переименовать планету', $check];
			}

			if ($taskKey == 'friends_count') {
				$result['task'][] = ['Кол-во друзей в игре: ' . $taskVal, $check];
			}

			if ($taskKey == 'ally') {
				$result['task'][] = ['Вступить в альянс с кол-во игроков: ' . $taskVal, $check];
			}

			if ($taskKey == 'storage' && $taskVal === true) {
				$result['task'][] = ['Построить любое хранилище ресурсов', $check];
			}

			if ($taskKey == 'trade') {
				$result['task'][] = ['Обменять ресурсы у торговца', $check];
			}

			if ($taskKey == 'fleet_mission') {
				$result['task'][] = ['Отправить флот в миссию: ' . __('main.type_mission.' . $taskVal), $check];
			}

			if ($taskKey == 'planets') {
				$result['task'][] = ['Кол-во колонизированных планет: ' . $taskVal, $check];
			}

			$errors += !$check ? 1 : 0;
		}

		if ($qInfo->finish > 0) {
			$errors++;
		}

		foreach ($quest[$id]['reward'] as $rewardKey => $rewardVal) {
			if ($rewardKey == 'metal') {
				$result['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.metal') . 'а';
			} elseif ($rewardKey == 'crystal') {
				$result['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.crystal') . 'а';
			} elseif ($rewardKey == 'deuterium') {
				$result['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.deuterium');
			} elseif ($rewardKey == 'credits') {
				$result['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.credits');
			} elseif ($rewardKey == 'build') {
				foreach ($rewardVal as $element => $level) {
					$type = Vars::getItemType($element);

					if ($type == ItemType::TECH) {
						$result['rewd'][] = 'Исследование <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня';
					} elseif ($type == ItemType::FLEET) {
						$result['rewd'][] = $level . ' ед. флота типа <b>' . __('main.tech.' . $element) . '</b>';
					} elseif ($type == ItemType::DEFENSE) {
						$result['rewd'][] = $level . ' ед. обороны типа <b>' . __('main.tech.' . $element) . '</b>';
					} elseif ($type == ItemType::OFFICIER) {
						$result['rewd'][] = 'Офицер <b>' . __('main.tech.' . $element) . '</b> на ' . round($level / 3600 / 24, 1) . ' суток';
					} else {
						$result['rewd'][] = 'Постройка <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня';
					}
				}
			} elseif ($rewardKey == 'STORAGE_RAND') {
				$result['rewd'][] = '+1 уровень одного из хранилищ ресурсов';
			}
		}

		$result['rewd'] = implode(', ', $result['rewd']);
		$result['errors'] = $errors > 0;

		return $result;
	}

	public function finish(int $id)
	{
		if ($id <= 0) {
			throw new Exception('Не выбрано задание');
		}

		$quest = require resource_path('engine/quests.php');

		if (!isset($quest[$id])) {
			throw new Exception('Задание не существует');
		}

		$qInfo = $this->user->quests()
			->where('quest_id', $id)
			->first();

		if (!$qInfo) {
			throw new Exception('Задание не существует');
		}

		$errors = 0;
		$checks = $qInfo->checkFinished($this->user, $this->planet);

		foreach ($quest[$id]['task'] as $taskKey => $taskVal) {
			$errors += !($checks[$taskKey] ?? false) ? 1 : 0;
		}

		if ($errors || $qInfo->finish) {
			throw new Exception('Задание не выполнено');
		}

		foreach ($quest[$id]['reward'] as $rewardKey => $rewardVal) {
			if ($rewardKey == 'metal') {
				$this->planet->metal += $rewardVal;
			} elseif ($rewardKey == 'crystal') {
				$this->planet->crystal += $rewardVal;
			} elseif ($rewardKey == 'deuterium') {
				$this->planet->deuterium += $rewardVal;
			} elseif ($rewardKey == 'credits') {
				$this->user->credits += $rewardVal;
			} elseif ($rewardKey == 'build') {
				foreach ($rewardVal as $element => $level) {
					$type = Vars::getItemType($element);

					if ($type == ItemType::TECH) {
						$this->user->setTech($element, $this->user->getTechLevel($element) + (int) $level);
					} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
						$this->planet->updateAmount($element, $level, true);
					} elseif ($type == ItemType::OFFICIER) {
						if ($this->user->{Vars::getName($element)}?->isFuture()) {
							$this->user->{Vars::getName($element)} = $this->user->{Vars::getName($element)}->addSeconds($level);
						} else {
							$this->user->{Vars::getName($element)} = now()->addSeconds($level);
						}
					} elseif ($type == ItemType::BUILDING) {
						$this->planet->updateAmount($element, (int) $level, true);
					}
				}
			} elseif ($rewardKey == 'storage_rand') {
				$this->planet->updateAmount(random_int(22, 24), 1, true);
			}
		}

		$qInfo->finish = true;
		$qInfo->update();

		cache()->forget('app::quests::' . $this->user->id);

		$this->user->save();
		$this->planet->save();
	}
}
