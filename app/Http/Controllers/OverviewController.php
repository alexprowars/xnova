<?php

namespace App\Http\Controllers;

use App\Http\Resources\FleetRow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Exceptions\ErrorException;
use App\Exceptions\RedirectException;
use App\Game;
use App\Helpers;
use App\Models;
use App\Models\Planet;
use App\Planet\Entity as PlanetEntity;
use App\Queue;
use App\Controller;
use App\Vars;

class OverviewController extends Controller
{
	public function delete(Request $request)
	{
		if ($request->isMethod('post') && $request->post('id') && $request->post('id', 0) == $this->user->planet_current) {
			if ($this->user->id != $this->planet->user_id) {
				throw new RedirectException('/overview/rename', "Удалить планету может только владелец");
			}

			if ($this->user->planet_id == $this->user->planet_current) {
				throw new RedirectException('/overview/rename', __('overview.deletemessage_wrong'));
			}

			if (!Hash::check(trim($request->post('pw')), $request->post('password'))) {
				throw new RedirectException('/overview/delete', __('overview.deletemessage_fail'));
			}

			$checkFleets = Models\Fleet::query()
				->where(function (Builder $query) {
					$query->where('start_galaxy', $this->planet->galaxy)
						->where('start_system', $this->planet->system)
						->where('start_planet', $this->planet->planet)
						->where('start_type', $this->planet->planet_type);
				})
				->orWhere(function (Builder $query) {
					$query->where('end_galaxy', $this->planet->galaxy)
						->where('end_system', $this->planet->system)
						->where('end_planet', $this->planet->planet)
						->where('end_type', $this->planet->planet_type);
				})
				->exists();

			if ($checkFleets) {
				throw new RedirectException('/overview/rename', 'Нельзя удалять планету если с/на неё летит флот');
			}

			$destruyed = now()->addDay();

			$this->planet->destruyed = $destruyed;
			$this->planet->user_id = null;
			$this->planet->update();

			$this->user->planet_current = $this->user->planet_id;
			$this->user->update();

			if ($this->planet->parent_planet) {
				Models\Planet::where('id', $this->planet->parent_planet)
					->update([
						'destruyed' => $destruyed,
						'user_id' => null,
					]);

				Models\Queue::where('planet_id', $this->planet->parent_planet)
					->delete();
			}

			Models\Queue::where('planet_id', $this->planet->id)
				->delete();

			Cache::forget('app::planetlist_' . $this->user->id);

			throw new RedirectException('/overview', __('overview.deletemessage_ok'));
		}

		$parse['number_1'] 		= mt_rand(1, 100);
		$parse['number_2'] 		= mt_rand(1, 100);
		$parse['number_3'] 		= mt_rand(1, 100);
		$parse['number_check'] 	= md5($parse['number_1'] + $parse['number_2'] * $parse['number_3']);

		$parse['id'] = $this->planet->id;
		$parse['galaxy'] = $this->planet->galaxy;
		$parse['system'] = $this->planet->system;
		$parse['planet'] = $this->planet->planet;

		return response()->state($parse);
	}

	public function rename(Request $request)
	{
		$parse = [];
		$parse['planet_id'] = $this->planet->id;
		$parse['galaxy_galaxy'] = $this->planet->galaxy;
		$parse['galaxy_system'] = $this->planet->system;
		$parse['galaxy_planet'] = $this->planet->planet;

		$parse['images'] = [
			'trocken' => 20,
			'wuesten' => 4,
			'dschjungel' => 19,
			'normaltemp' => 15,
			'gas' => 16,
			'wasser' => 18,
			'eis' => 20
		];

		$parse['type'] = '';

		foreach ($parse['images'] as $type => $max) {
			if (str_contains($this->planet->image, $type)) {
				$parse['type'] = $type;
			}
		}

		if ($request->post('action')) {
			$action = $request->post('action');

			if ($action == 'name') {
				$name = strip_tags(trim($request->post('name', '')));

				if ($name == '') {
					throw new ErrorException('Ввведите новое название планеты');
				}

				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
					throw new ErrorException('Введённое название содержит недопустимые символы');
				}

				if (mb_strlen($name) <= 1 || mb_strlen($name) >= 20) {
					throw new ErrorException('Введённо слишком длинное или короткое название планеты');
				}

				$this->planet->name = $name;
				$this->planet->update();

				throw new RedirectException('/overview', 'Название планеты изменено');
			} elseif ($action == 'image') {
				if ($this->user->credits < 1) {
					throw new ErrorException('Недостаточно кредитов');
				}

				$image = (int) $request->post('image', 0);

				if ($image <= 0 || $image > $parse['images'][$parse['type']]) {
					throw new ErrorException('Недостаточно читерских навыков');
				}

				$this->planet->image = $parse['type'] . 'planet' . ($image < 10 ? '0' : '') . $image;
				$this->planet->update();

				$this->user->credits--;
				$this->user->update();

				throw new RedirectException('/overview', 'Картинка планеты изменена');
			}
		}

		$parse['planet_name'] = $this->planet->name;

		return $parse;
	}

	public function daily()
	{
		if ($this->user->daily_bonus?->isFuture()) {
			throw new ErrorException('Вы не можете получить ежедневный бонус в данное время');
		}

		$factor = $this->user->daily_bonus_factor < 50
			? $this->user->daily_bonus_factor + 1 : 50;

		if (!$this->user->daily_bonus || $this->user->daily_bonus->subDay()->isPast()) {
			$factor = 1;
		}

		$add = $factor * 500 * Game::getSpeed('mine');

		$this->planet->metal += $add;
		$this->planet->crystal += $add;
		$this->planet->deuterium += $add;
		$this->planet->update();

		$this->user->daily_bonus = now()->addSeconds(86400);
		$this->user->daily_bonus_factor = $factor;

		if ($this->user->daily_bonus_factor > 1) {
			$this->user->credits++;
		}

		$this->user->update();

		throw new RedirectException('/overview', 'Спасибо за поддержку!<br>Вы получили в качестве бонуса по <b>' . $add . '</b> Металла, Кристаллов и Дейтерия' . ($this->user->daily_bonus_factor > 1 ? ', а также 1 кредит.' : ''));
	}

	public function index()
	{
		$XpMinierUp = $this->user->lvl_minier ** 3;
		$XpRaidUp = $this->user->lvl_raid ** 2;

		$fleets = Models\Fleet::query()
			->where('user_id', $this->user->id)
			->orWhere('target_user_id', $this->user->id)
			->with('user')
			->get();

		$fpage = [];
		$aks = [];

		foreach ($fleets as $fleet) {
			if ($fleet->user_id == $this->user->id) {
				if ($fleet->start_time->isFuture()) {
					$fpage[$fleet->start_time->getTimestamp()][$fleet->id] = FleetRow::make($fleet, 0, true);
				}

				if ($fleet->end_stay?->isFuture()) {
					$fpage[$fleet->end_stay->getTimestamp()][$fleet->id] = FleetRow::make($fleet, 1, true);
				}

				if (!($fleet->mission == 7 && $fleet->mess == 0)) {
					if (($fleet->end_time->isFuture() and $fleet->mission != 4) or ($fleet->mess == 1 and $fleet->mission == 4)) {
						$fpage[$fleet->end_time->getTimestamp()][$fleet->id] = FleetRow::make($fleet, 2, true);
					}
				}

				if ($fleet->assault_id && !in_array($fleet->assault_id, $aks)) {
					$AKSFleets = Models\Fleet::query()
						->where('assault_id', $fleet->assault_id)
						->where('user_id', '!=', $this->user->id)
						->where('mess', 0)
						->get();

					foreach ($AKSFleets as $AKFleet) {
						$fpage[$fleet->start_time->getTimestamp()][$AKFleet->id] = FleetRow::make($AKFleet, 0, false);
					}

					$aks[] = $fleet->assault_id;
				}
			} elseif ($fleet->mission != 8) {
				if ($fleet->start_time->isFuture()) {
					$fpage[$fleet->start_time->getTimestamp()][$fleet->id] = FleetRow::make($fleet, 0, false);
				}

				if ($fleet->mission == 5 && $fleet->end_stay?->isFuture()) {
					$fpage[$fleet->end_stay->getTimestamp()][$fleet->id] = FleetRow::make($fleet, 1, false);
				}
			}
		}

		$parse = [];
		$parse['moon'] = false;

		if ($this->planet->parent_planet != 0 && $this->planet->planet_type != 3 && $this->planet->id) {
			$lune = Cache::remember('app::lune_' . $this->planet->parent_planet, 300, function () {
				return Models\Planet::query()
					->select(['id', 'name', 'image', 'destruyed'])
					->where('id', $this->planet->parent_planet)
					->where('planet_type', 3)
					->first()?->toArray();
			});

			if (isset($lune['id']) && empty($lune['destruyed'])) {
				$parse['moon'] = [
					'id' => $lune['id'],
					'name' => $lune['name'],
					'image' => $lune['image']

				];
			}
		}

		$records = Cache::remember('app::records_' . $this->user->id, 1800, function () {
			return Models\Statistic::query()
				->select(['build_points', 'tech_points', 'fleet_points', 'defs_points', 'total_points', 'total_old_rank', 'total_rank'])
				->where('stat_type', 1)
				->where('stat_code', 1)
				->where('user_id', $this->user->id)
				->first()?->toArray();
		});

		$parse['points'] = [
			'build' => 0,
			'tech' => 0,
			'fleet' => 0,
			'defs' => 0,
			'total' => 0,
			'place' => 0,
			'diff' => 0
		];

		if ($records) {
			if (!$records['total_old_rank']) {
				$records['total_old_rank'] = $records['total_rank'];
			}

			$parse['points']['build'] = (int) $records['build_points'];
			$parse['points']['tech'] = (int) $records['tech_points'];
			$parse['points']['fleet'] = (int) $records['fleet_points'];
			$parse['points']['defs'] = (int) $records['defs_points'];
			$parse['points']['total'] = (int) $records['total_points'];
			$parse['points']['place'] = (int) $records['total_rank'];
			$parse['points']['diff'] = (int) $records['total_old_rank'] - (int) $records['total_rank'];
		}

		$flotten = [];

		if (count($fpage) > 0) {
			ksort($fpage);
			foreach ($fpage as $content) {
				foreach ($content as $text) {
					$flotten[] = $text;
				}
			}
		}

		$parse['fleets'] = $flotten;

		$parse['debris'] = [
			'metal' => $this->planet->debris_metal,
			'crystal' => $this->planet->debris_crystal,
		];

		$parse['debris_mission'] = (($this->planet->debris_metal != 0 || $this->planet->debris_crystal != 0) && $this->planet->getLevel('recycler') > 0);

		$queueList = [];

		$planetsData = Planet::query()
			->where('user_id', $this->user->id)
			->get()->keyBy('id');

		$queueManager = new Queue($this->user);

		if ($queueManager->getCount($queueManager::TYPE_BUILDING)) {
			$queueArray = $queueManager->get($queueManager::TYPE_BUILDING);

			$end = [];

			foreach ($queueArray as $item) {
				$end[$item->planet_id] ??= $item->time;

				/** @var Planet $planet */
				$planet = $planetsData[$item->planet_id];
				$planet->setRelation('user', $this->user);

				$entity = PlanetEntity\Building::createEntity(
					$item->object_id,
					$item->level - ($item->operation == $item::OPERATION_BUILD ? 1 : 0),
					$planet
				);

				$time = $entity->getTime();

				if ($item->operation == $item::OPERATION_DESTROY) {
					$time = ceil($time / 2);
				}

				$end[$item->planet_id] += $time;

				$queueList[] = [
					'time' => $end[$item->planet_id],
					'planet_id' => $item->planet_id,
					'planet_name' => $planet->name,
					'object_id' => $item->object_id,
					'level' => $item->operation == $item::OPERATION_BUILD ? $item->level - 1 : $item->level + 1,
					'level_to' => $item->level,
				];
			}
		}

		if ($queueManager->getCount($queueManager::TYPE_RESEARCH)) {
			$queueArray = $queueManager->get($queueManager::TYPE_RESEARCH);

			foreach ($queueArray as $item) {
				$queueList[] = [
					'time' => (int) $item->time_end->timestamp,
					'planet_id' => $item->planet_id,
					'planet_name' => $planetsData[$item->planet_id]->name,
					'object_id' => $item->object_id,
					'level' => $this->user->getTechLevel($item->object_id),
					'level_to' => $this->user->getTechLevel($item->object_id) + 1,
				];
			}
		}

		if ($queueManager->getCount($queueManager::TYPE_SHIPYARD)) {
			$queueArray = $queueManager->get($queueManager::TYPE_SHIPYARD);

			$end = [];

			foreach ($queueArray as $item) {
				$end[$item->planet_id] ??= $item->time;
				$end[$item->planet_id] += ($item->time_end->timestamp - $item->time->timestamp) * $item->level;

				if ($end[$item->planet_id] < time()) {
					continue;
				}

				$queueList[] = [
					'time' => $end[$item->planet_id],
					'planet_id' => $item->planet_id,
					'planet_name' => $planetsData[$item->planet_id]->name,
					'object_id' => $item->object_id,
					'level' => $item->level,
				];
			}
		}

		usort($queueList, fn($a, $b) => $a['time'] > $b['time'] ? 1 : -1);

		$parse['build_list'] = $queueList;

		$parse['lvl'] = [
			'mine' => [
				'p' => $this->user->xpminier,
				'l' => $this->user->lvl_minier,
				'u' => (int) $XpMinierUp,
			],
			'raid' => [
				'p' => $this->user->xpraid,
				'l' => $this->user->lvl_raid,
				'u' => (int) $XpRaidUp
			]
		];

		$parse['links'] = $this->user->links;
		$parse['refers'] = $this->user->refers;
		$parse['noob'] = config('settings.noobprotection', 0);

		$parse['raids'] = [
			'win' => $this->user->raids_win,
			'lost' => $this->user->raids_lose,
			'total' => $this->user->raids
		];

		$parse['bonus'] = $this->user->daily_bonus->isPast();

		if ($parse['bonus']) {
			$bonus = $this->user->daily_bonus_factor + 1;

			if ($bonus > 50) {
				$bonus = 50;
			}

			if ($this->user->daily_bonus->subDay()->isPast()) {
				$bonus = 1;
			}

			$parse['bonus_count'] = $bonus * 500 * Game::getSpeed('mine');
		}

		$parse['chat'] = [];

		if (Helpers::isMobile()) {
			$chatCached = Cache::remember('chat.cache', 86400, function () {
				$messages = Models\Chat::orderByDesc('created_at')
					->limit(20)->with('user')->get();

				$chat = [];

				foreach ($messages as $message) {
					if (preg_match_all("/приватно [(.*?)]/u", $message->message, $private)) {
						$message->message = preg_replace("/приватно [(.*?)]/u", '', $message->message);
					}

					if (preg_match_all("/для [(.*?)]/u", $message->message, $to)) {
						$message->message = preg_replace("/для [(.*?)]/u", '', $message->message);

						if (isset($private[1]) && count($private[1]) > 0) {
							$private[1] = array_merge($private[1], $to[1]);
							unset($to[1]);
						}
					}

					if (!isset($to[1])) {
						$to[1] = [];
					}

					$isPrivate = false;

					if (isset($private['1']) && count($private[1]) > 0) {
						$to[1] = $private[1];
						$isPrivate = true;
					}

					$message->message = trim($message->message);

					$chat[] = [$message->id, $message->created_at, $message->user->username, $to[1], $isPrivate, $message->message, 0];
				}

				return json_encode(array_reverse($chat));
			});

			if (is_string($chatCached)) {
				$chat = json_decode($chatCached, true);
			} else {
				$chat = null;
			}

			if ($chat && count($chat)) {
				$chat = array_reverse($chat);

				$i = 0;

				foreach ($chat as $message) {
					if ($message[4] != false) {
						continue;
					}

					if ($i >= 5) {
						break;
					}

					$t = explode(' ', $message[5]);

					foreach ($t as $j => $w) {
						if (mb_strlen($w, 'UTF-8') > 30) {
							$w = str_split(iconv('utf-8', 'windows-1251', $w), 30);

							$t[$j] = iconv('windows-1251', 'utf-8', implode(' ', $w));
						}
					}

					$message[5] = implode(' ', $t);

					$parse['chat'][] = [
						'time' => $message[1],
						'message' => '<span class="title"><span class="to">' . $message[2] . '</span> написал' . (count($message[3]) ? ' <span class="to">' . implode(', ', $message[3]) . '</span>' : '') . '</span>: ' . $message[5] . ''
					];

					$i++;
				}
			}
		}

		$showMessage = false;

		foreach (Vars::getResources() as $res) {
			if ($this->planet->getLevel($res . '_mine') && !$this->planet->getEntity($res . '_mine')->factor) {
				$showMessage = true;
			}
		}

		$parse['error'] = false;

		if ($showMessage) {
			$parse['error'] = '<span class="negative">Одна из шахт находится в выключенном состоянии. Зайдите в меню "<a href="' . URL::route('resources', [], false) . '">Сырьё</a>" и восстановите производство.</span>';
		}

		return response()->state($parse);
	}
}
