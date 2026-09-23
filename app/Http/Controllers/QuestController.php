<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Format;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
			$result['items'][] = [
				'id' => $questId,
				'title' => __('quests.quests.' . $questId . '.title'),
				'finish' => isset($userQuests[$questId]) && $userQuests[$questId]['finish'] == 1,
				'required' => $quest['required'],
				'available' => $this->meetsRequirements($quest['required'] ?? []),
			];
		}

		usort($result['items'], fn (array $first, array $second) => $first['finish'] <=> $second['finish']);

		return Inertia::render('Quests/List', $result);
	}

	public function info(int $id)
	{
		if ($id <= 0) {
			throw new Exception(__('quests.no_quest_selected'));
		}

		$quest = require resource_path('engine/quests.php');

		if (!isset($quest[$id])) {
			throw new Exception(__('quests.quest_not_found'));
		}

		$result = [
			'id' => $id,
			'title' => __('quests.quests.' . $id . '.title'),
			'description' => __('quests.quests.' . $id . '.description'),
			'solution' => __('quests.quests.' . $id . '.solution'),
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
						$result['task'][] = [__('quests.task_research', ['element' => __('main.tech.' . $element), 'level' => $level]), $chk];
					} elseif ($type == ItemType::FLEET) {
						$result['task'][] = [__('quests.task_fleet', ['amount' => $level, 'element' => __('main.tech.' . $element)]), $chk];
					} elseif ($type == ItemType::DEFENSE) {
						$result['task'][] = [__('quests.task_defense', ['amount' => $level, 'element' => __('main.tech.' . $element)]), $chk];
					} else {
						$result['task'][] = [__('quests.task_build', ['element' => __('main.tech.' . $element), 'level' => $level]), $chk];
					}
				}
			}

			if ($taskKey == '!planet_name') {
				$result['task'][] = [__('quests.task_rename_planet'), $check];
			}

			if ($taskKey == 'friends_count') {
				$result['task'][] = [__('quests.task_friends_count', ['count' => $taskVal]), $check];
			}

			if ($taskKey == 'ally') {
				$result['task'][] = [__('quests.task_ally', ['count' => $taskVal]), $check];
			}

			if ($taskKey == 'storage' && $taskVal === true) {
				$result['task'][] = [__('quests.task_storage'), $check];
			}

			if ($taskKey == 'trade') {
				$result['task'][] = [__('quests.task_trade'), $check];
			}

			if ($taskKey == 'fleet_mission') {
				$result['task'][] = [__('quests.task_fleet_mission', ['mission' => __('main.type_mission.' . $taskVal->value)]), $check];
			}

			if ($taskKey == 'planets') {
				$result['task'][] = [__('quests.task_planets', ['count' => $taskVal]), $check];
			}

			$errors += !$check ? 1 : 0;
		}

		if ($qInfo->finish > 0 || !$this->meetsRequirements($quest[$id]['required'] ?? [])) {
			$errors++;
		}

		foreach ($quest[$id]['reward'] as $rewardKey => $rewardVal) {
			if ($rewardKey == 'metal') {
				$result['rewd'][] = __('quests.reward_metal', ['amount' => Format::number($rewardVal)]);
			} elseif ($rewardKey == 'crystal') {
				$result['rewd'][] = __('quests.reward_crystal', ['amount' => Format::number($rewardVal)]);
			} elseif ($rewardKey == 'deuterium') {
				$result['rewd'][] = __('quests.reward_deuterium', ['amount' => Format::number($rewardVal)]);
			} elseif ($rewardKey == 'credits') {
				$result['rewd'][] = __('quests.reward_credits', ['amount' => Format::number($rewardVal)]);
			} elseif ($rewardKey == 'build') {
				foreach ($rewardVal as $element => $level) {
					$type = Vars::getItemType($element);

					if ($type == ItemType::TECH) {
						$result['rewd'][] = __('quests.reward_research', ['element' => __('main.tech.' . $element), 'level' => $level]);
					} elseif ($type == ItemType::FLEET) {
						$result['rewd'][] = __('quests.reward_fleet', ['amount' => $level, 'element' => __('main.tech.' . $element)]);
					} elseif ($type == ItemType::DEFENSE) {
						$result['rewd'][] = __('quests.reward_defense', ['amount' => $level, 'element' => __('main.tech.' . $element)]);
					} else {
						$result['rewd'][] = __('quests.reward_build', ['element' => __('main.tech.' . $element), 'level' => $level]);
					}
				}
			} elseif ($rewardKey == 'officier') {
				foreach ($rewardVal as $code => $duration) {
					$result['rewd'][] = __('quests.reward_officer', ['officer' => __('officier.items.' . $code), 'days' => round($duration / 86400, 1)]);
				}
			} elseif ($rewardKey == 'storage_rand') {
				$result['rewd'][] = __('quests.reward_storage');
			}
		}

		$result['rewd'] = implode(', ', $result['rewd']);
		$result['errors'] = $errors > 0;

		return Inertia::render('Quests/Detail', $result);
	}

	public function finish(int $id)
	{
		if ($id <= 0) {
			throw new Exception(__('quests.no_quest_selected'));
		}

		$quest = require resource_path('engine/quests.php');

		if (!isset($quest[$id])) {
			throw new Exception(__('quests.quest_not_found'));
		}

		DB::transaction(function () use ($id, $quest) {
			$this->user->refreshForUpdate();
			$this->planet->refreshForUpdate();

			$qInfo = $this->user->quests()
				->where('quest_id', $id)
				->lockForUpdate()
				->first();

			if (!$qInfo) {
				throw new Exception(__('quests.quest_not_found'));
			}

			$errors = 0;
			$checks = $qInfo->checkFinished($this->user, $this->planet);

			foreach ($quest[$id]['task'] as $taskKey => $taskVal) {
				$errors += !($checks[$taskKey] ?? false) ? 1 : 0;
			}

			if ($errors || $qInfo->finish || !$this->meetsRequirements($quest[$id]['required'] ?? [])) {
				throw new Exception(__('quests.quest_not_completed'));
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
						} elseif ($type == ItemType::BUILDING) {
							$this->planet->updateAmount($element, (int) $level, true);
						}
					}
				} elseif ($rewardKey == 'officier') {
					foreach ($rewardVal as $code => $duration) {
						if (!in_array($code, Vars::getOfficiers(), true)) {
							throw new Exception(__('quests.unknown_reward_officer'));
						}

						$attribute = 'officier_' . $code;
						$expiresAt = $this->user->{$attribute};

						$this->user->{$attribute} = ($expiresAt?->isFuture() ? $expiresAt : now())->addSeconds($duration);
					}
				} elseif ($rewardKey == 'storage_rand') {
					$this->planet->updateAmount(random_int(22, 24), 1, true);
				}
			}

			$qInfo->finish = true;
			$qInfo->update();

			$this->user->save();
			$this->planet->save();
		});

		cache()->forget('app::quests::' . $this->user->id);

		return to_route('quests');
	}

	/**
	 * @param array{quest?: int, level_minier?: int, level_raid?: int} $requirements
	 */
	private function meetsRequirements(array $requirements): bool
	{
		foreach ($requirements as $key => $value) {
			$satisfied = match ($key) {
				'quest' => (bool) $this->user->quests->firstWhere('quest_id', $value)?->finish,
				'level_minier' => $this->user->lvl_minier >= $value,
				'level_raid' => $this->user->lvl_raid >= $value,
			};

			if (!$satisfied) {
				return false;
			}
		}

		return true;
	}
}
