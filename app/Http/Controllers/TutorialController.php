<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\Vars;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Format;

class TutorialController extends Controller
{
	public function index()
	{
		$quests = require resource_path('engine/tutorial.php');

		$parse = [];

		$userQuests = [];

		foreach ($this->user->quests as $quest) {
			$userQuests[$quest->quest_id] = $quest->toArray();
		}

		$parse['items'] = [];
		$parse['quests'] = $userQuests;

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

			$parse['items'][] = [
				'id' => $questId,
				'title' => __('tutorial.' . $questId . '.title'),
				'finish' => isset($userQuests[$questId]) && $userQuests[$questId]['finish'] == 1,
				'required' => $quest['required'],
				'available' => $available,
			];
		}

		return $parse;
	}

	public function info(int $id)
	{
		if ($id <= 0) {
			throw new PageException('Не выбрано задание', '/tutorial');
		}

		$tutorial = require resource_path('engine/tutorial.php');

		if (!isset($tutorial[$id])) {
			throw new PageException('Задание не существует', '/tutorial');
		}

		$parse = [];
		$parse['id'] = $id;
		$parse['title'] = __('tutorial.' . $id . '.title');
		$parse['description'] = __('tutorial.' . $id . '.description');
		$parse['solution'] = __('tutorial.' . $id . '.solution');
		$parse['task'] = [];
		$parse['rewd'] = [];

		$qInfo = $this->user->quests()
			->where('quest_id', $id)
			->first();

		if (!$qInfo) {
			$qInfo = $this->user->quests()->create([
				'quest_id' 	=> $id,
				'finish' 	=> 0,
				'stage' 	=> 0,
			]);
		}

		$errors = 0;
		$checks = $qInfo->checkFinished($this->user, $this->planet);

		foreach ($tutorial[$id]['task'] as $taskKey => $taskVal) {
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
						$parse['task'][] = ['Исследовать <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня', $chk];
					} elseif ($type == ItemType::FLEET) {
						$parse['task'][] = ['Построить ' . $level . ' ед. флота типа <b>' . __('main.tech.' . $element) . '</b>', $chk];
					} elseif ($type == ItemType::DEFENSE) {
						$parse['task'][] = ['Построить ' . $level . ' ед. обороны типа <b>' . __('main.tech.' . $element) . '</b>', $chk];
					} else {
						$parse['task'][] = ['Построить <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня', $chk];
					}
				}
			}

			if ($taskKey == '!planet_name') {
				$parse['task'][] = ['Переименовать планету', $check];
			}

			if ($taskKey == 'buddy_count') {
				$parse['task'][] = ['Кол-во друзей в игре: ' . $taskVal, $check];
			}

			if ($taskKey == 'ally') {
				$parse['task'][] = ['Вступить в альянс с кол-во игроков: ' . $taskVal, $check];
			}

			if ($taskKey == 'storage' && $taskVal === true) {
				$parse['task'][] = ['Построить любое хранилище ресурсов', $check];
			}

			if ($taskKey == 'trade') {
				$parse['task'][] = ['Обменять ресурсы у торговца', $check];
			}

			if ($taskKey == 'fleet_mission') {
				$parse['task'][] = ['Отправить флот в миссию: ' . __('main.type_mission.' . $taskVal), $check];
			}

			if ($taskKey == 'planets') {
				$parse['task'][] = ['Кол-во колонизированных планет: ' . $taskVal, $check];
			}

			$errors += !$check ? 1 : 0;
		}

		if ($qInfo->finish > 0) {
			$errors++;
		}

		foreach ($tutorial[$id]['reward'] as $rewardKey => $rewardVal) {
			if ($rewardKey == 'metal') {
				$parse['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.Metal') . 'а';
			} elseif ($rewardKey == 'crystal') {
				$parse['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.Crystal') . 'а';
			} elseif ($rewardKey == 'deuterium') {
				$parse['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.Deuterium');
			} elseif ($rewardKey == 'credits') {
				$parse['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.Credits');
			} elseif ($rewardKey == 'build') {
				foreach ($rewardVal as $element => $level) {
					$type = Vars::getItemType($element);

					if ($type == ItemType::TECH) {
						$parse['rewd'][] = 'Исследование <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня';
					} elseif ($type == ItemType::FLEET) {
						$parse['rewd'][] = $level . ' ед. флота типа <b>' . __('main.tech.' . $element) . '</b>';
					} elseif ($type == ItemType::DEFENSE) {
						$parse['rewd'][] = $level . ' ед. обороны типа <b>' . __('main.tech.' . $element) . '</b>';
					} elseif ($type == ItemType::OFFICIER) {
						$parse['rewd'][] = 'Офицер <b>' . __('main.tech.' . $element) . '</b> на ' . round($level / 3600 / 24, 1) . ' суток';
					} else {
						$parse['rewd'][] = 'Постройка <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня';
					}
				}
			} elseif ($rewardKey == 'STORAGE_RAND') {
				$parse['rewd'][] = '+1 уровень одного из хранилищ ресурсов';
			}
		}

		$parse['rewd'] = implode(', ', $parse['rewd']);
		$parse['errors'] = $errors > 0;

		return $parse;
	}

	public function finish(int $id)
	{
		if ($id <= 0) {
			throw new Exception('Не выбрано задание');
		}

		$tutorial = require resource_path('engine/tutorial.php');

		if (!isset($tutorial[$id])) {
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

		foreach ($tutorial[$id]['task'] as $taskKey => $taskVal) {
			$errors += !($checks[$taskKey] ?? false) ? 1 : 0;
		}

		if ($errors || $qInfo->finish) {
			throw new Exception('Задание не выполнено');
		}

		foreach ($tutorial[$id]['reward'] as $rewardKey => $rewardVal) {
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

		$qInfo->finish = 1;
		$qInfo->update();

		cache()->forget('app::quests::' . $this->user->id);

		$this->user->save();
		$this->planet->save();
	}
}
