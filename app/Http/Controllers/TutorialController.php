<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\Vars;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Format;
use App\Models;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class TutorialController extends Controller
{
	public function info(int $stage)
	{
		if ($stage <= 0) {
			throw new PageException('Не выбрано задание', '/tutorial/');
		}

		if (!__('tutorial.tutorial.' . $stage)) {
			throw new PageException('Задание не существует', '/tutorial/');
		}

		$parse['info'] = __('tutorial.tutorial.' . $stage);
		$parse['task'] = [];
		$parse['rewd'] = [];

		$qInfo = $this->user->quests()
			->where('quest_id', $stage)
			->first();

		if (!$qInfo) {
			$qInfo = $this->user->quests()->create([
				'quest_id' 	=> $stage,
				'finish' 	=> 0,
				'stage' 	=> 0
			]);
		}

		$errors = 0;

		foreach ($parse['info']['TASK'] as $taskKey => $taskVal) {
			$check = false;

			if ($taskKey == 'BUILD') {
				$chk = true;

				foreach ($taskVal as $element => $level) {
					$type = Vars::getItemType($element);

					if ($type == ItemType::TECH) {
						$check = $this->user->getTechLevel($element) >= $level;
					} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
						$check = $this->planet->getLevel($element) >= $level;
					} else {
						$check = $this->planet->getLevel($element) >= $level;
					}

					if ($chk == true) {
						$chk = $check;
					}

					if ($type == ItemType::TECH) {
						$parse['task'][] = ['Исследовать <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня', $check];
					} elseif ($type == ItemType::FLEET) {
						$parse['task'][] = ['Построить ' . $level . ' ед. флота типа <b>' . __('main.tech.' . $element) . '</b>', $check];
					} elseif ($type == ItemType::DEFENSE) {
						$parse['task'][] = ['Построить ' . $level . ' ед. обороны типа <b>' . __('main.tech.' . $element) . '</b>', $check];
					} else {
						$parse['task'][] = ['Построить <b>' . __('main.tech.' . $element) . '</b> ' . $level . ' уровня', $check];
					}
				}

				$check = $chk;
			}

			if ($taskKey == '!PLANET_NAME') {
				$check = $this->planet->name != $taskVal ? true : false;

				$parse['task'][] = ['Переименовать планету', $check];
			}

			if ($taskKey == 'BUDDY_COUNT') {
				$count = Models\Friend::query()->where('user_id', $this->user->id)->orWhere('friend_id', $this->user->id)->count();

				$check = $count >= $taskVal;

				$parse['task'][] = ['Кол-во друзей в игре: ' . $taskVal, $check];
			}

			if ($taskKey == 'ALLY') {
				$check = $this->user->alliance_id > 0;

				$parse['task'][] = ['Вступить в альянс с кол-во игроков: ' . $taskVal, $check];
			}

			if ($taskKey == 'STORAGE') {
				if ($taskVal === true) {
					$check = $this->planet->getLevel('metal_store') > 0 || $this->planet->getLevel('crystal_store') > 0 || $this->planet->getLevel('deuterium_store') > 0;

					$parse['task'][] = ['Построить любое хранилище ресурсов', $check];
				}
			}

			if ($taskKey == 'TRADE') {
				$check = $qInfo->stage > 0;

				$parse['task'][] = ['Обменять ресурсы у торговца', $check];
			}

			if ($taskKey == 'FLEET_MISSION') {
				$check = $qInfo->stage > 0;

				$parse['task'][] = ['Отправить флот в миссию: ' . __('main.type_mission.' . $taskVal), $check];
			}

			if ($taskKey == 'PLANETS') {
				$count = $this->user->planets()
					->where('planet_type', 1)
					->count();

				$check = $count >= $taskVal;

				$parse['task'][] = ['Кол-во колонизированных планет: ' . $taskVal, $check];
			}

			$errors += !$check ? 1 : 0;
		}

		if ($qInfo->finish > 0) {
			$errors++;
		}

		if (Request::has('continue') && !$errors && $qInfo->finish == 0) {
			foreach ($parse['info']['REWARD'] as $rewardKey => $rewardVal) {
				if ($rewardKey == 'metal') {
					$this->planet->metal += $rewardVal;
				} elseif ($rewardKey == 'crystal') {
					$this->planet->crystal += $rewardVal;
				} elseif ($rewardKey == 'deuterium') {
					$this->planet->deuterium += $rewardVal;
				} elseif ($rewardKey == 'credits') {
					$this->user->credits += $rewardVal;
				} elseif ($rewardKey == 'BUILD') {
					foreach ($rewardVal as $element => $level) {
						$type = Vars::getItemType($element);

						if ($type == ItemType::TECH) {
							$this->user->setTech($element, $this->user->getTechLevel($element) + (int) $level);
						} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
							$this->planet->updateAmount($element, $level, true);
						} elseif ($type == ItemType::OFFICIER) {
							if ($this->user->{Vars::getName($element)} > time()) {
								$this->user->{Vars::getName($element)} += $level;
							} else {
								$this->user->{Vars::getName($element)} = time() + $level;
							}
						} elseif ($type == ItemType::BUILDING) {
							$this->planet->updateAmount($element, (int) $level, true);
						}
					}
				} elseif ($rewardKey == 'STORAGE_RAND') {
					$r = random_int(22, 24);

					$this->planet->updateAmount($r, 1, true);
				}
			}

			$qInfo->finish = 1;
			$qInfo->update();

			Cache::forget('app::quests::' . $this->user->id);

			$this->user->save();
			$this->planet->save();

			throw new RedirectException('/tutorial', 'Квест завершен');
		}

		foreach ($parse['info']['REWARD'] as $rewardKey => $rewardVal) {
			if ($rewardKey == 'metal') {
				$parse['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.Metal') . 'а';
			} elseif ($rewardKey == 'crystal') {
				$parse['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.Crystal') . 'а';
			} elseif ($rewardKey == 'deuterium') {
				$parse['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.Deuterium') . '';
			} elseif ($rewardKey == 'credits') {
				$parse['rewd'][] = Format::number($rewardVal) . ' ед. ' . __('main.Credits') . '';
			} elseif ($rewardKey == 'BUILD') {
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
		$parse['stage'] = $stage;
		$parse['errors'] = $errors;

		return response()->state($parse);
	}

	public function index()
	{
		$parse = [];

		$userQuests = [];

		foreach ($this->user->quests as $quest) {
			$userQuests[$quest->quest_id] = $quest->toArray();
		}

		$parse['list'] = [];
		$parse['quests'] = $userQuests;

		$quests = __('tutorial.tutorial');

		foreach ($quests as $qId => $quest) {
			$available = true;

			if (isset($quest['REQUIRED'])) {
				foreach ($quest['REQUIRED'] as $key => $req) {
					if ($key == 'QUEST' && (!isset($userQuests[$req]) || (isset($userQuests[$req]) && $userQuests[$req]['finish'] == 0))) {
						$available = false;
					}

					if ($key == 'LEVEL_MINIER' && $this->user->lvl_minier < $req) {
						$available = false;
					}

					if ($key == 'LEVEL_RAID' && $this->user->lvl_raid < $req) {
						$available = false;
					}
				}
			}

			$quest['ID'] = $qId;
			$quest['FINISH'] = (isset($userQuests[$qId]) && $userQuests[$qId]['finish'] == 1);
			$quest['AVAILABLE'] = $available;

			$parse['list'][] = $quest;
		}

		return response()->state($parse);
	}
}
