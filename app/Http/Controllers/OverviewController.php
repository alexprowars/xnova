<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use App\Exceptions\ErrorException;
use App\Exceptions\RedirectException;
use App\Fleet;
use App\Game;
use App\Helpers;
use App\Models;
use App\Planet;
use App\Queue;
use App\Models\Fleet as FleetModel;
use App\Controller;
use App\Vars;
use App\Entity;

class OverviewController extends Controller
{
	protected $loadPlanet = true;

	private function buildFleetEventTable(FleetModel $FleetRow, $Status, $Owner)
	{
		$FleetStyle = [
			1 => 'attack',
			2 => 'federation',
			3 => 'transport',
			4 => 'deploy',
			5 => 'transport',
			6 => 'espionage',
			7 => 'colony',
			8 => 'harvest',
			9 => 'destroy',
			10 => 'missile',
			15 => 'transport',
			20 => 'attack'
		];

		$FleetStatus = [0 => 'flight', 1 => 'holding', 2 => 'return'];
		$FleetPrefix = $Owner == true ? 'own' : '';

		$MissionType 	= $FleetRow->mission;

		$FleetContent 	= Fleet::CreateFleetPopupedFleetLink($FleetRow, __('overview.ov_fleet'), $FleetPrefix . $FleetStyle[$MissionType], $this->user);
		$FleetCapacity 	= Fleet::CreateFleetPopupedMissionLink($FleetRow, __('main.type_mission.' . $MissionType), $FleetPrefix . $FleetStyle[$MissionType]);

		$StartPlanet 	= $FleetRow->owner_name;
		$StartType 		= $FleetRow->start_type;
		$TargetPlanet 	= $FleetRow->target_owner_name;
		$TargetType 	= $FleetRow->end_type;

		$StartID  = '';
		$TargetID = '';

		if ($Status != 2) {
			if ($StartPlanet == '') {
				$StartID = ' с координат ';
			} else {
				if ($StartType == 1) {
					$StartID = __('overview.ov_planet_to');
				} elseif ($StartType == 3) {
					$StartID = __('overview.ov_moon_to');
				} elseif ($StartType == 5) {
					$StartID = ' с военной базы ';
				}

				$StartID .= $StartPlanet . " ";
			}

			$StartID .= $FleetRow->getStartAdressLink($FleetPrefix . $FleetStyle[$MissionType]);

			if ($TargetPlanet == '') {
				$TargetID = ' координаты ';
			} else {
				if ($MissionType != 15 && $MissionType != 5) {
					if ($TargetType == 1) {
						$TargetID = __('overview.ov_planet_to_target');
					} elseif ($TargetType == 2) {
						$TargetID = __('overview.ov_debris_to_target');
					} elseif ($TargetType == 3) {
						$TargetID = __('overview.ov_moon_to_target');
					} elseif ($TargetType == 5) {
						$TargetID = ' военной базе ';
					}
				} else {
					$TargetID = __('overview.ov_explo_to_target');
				}

				$TargetID .= $TargetPlanet . " ";
			}

			$TargetID .= $FleetRow->getTargetAdressLink($FleetPrefix . $FleetStyle[$MissionType]);
		} else {
			if ($StartPlanet == '') {
				$StartID = ' на координаты ';
			} else {
				if ($StartType == 1) {
					$StartID = __('overview.ov_back_planet');
				} elseif ($StartType == 3) {
					$StartID = __('overview.ov_back_moon');
				}

				$StartID .= $StartPlanet . " ";
			}

			$StartID .= $FleetRow->getStartAdressLink($FleetPrefix . $FleetStyle[$MissionType]);

			if ($TargetPlanet == '') {
				$TargetID = ' с координат ';
			} else {
				if ($MissionType != 15) {
					if ($TargetType == 1) {
						$TargetID = __('overview.ov_planet_from');
					} elseif ($TargetType == 2) {
						$TargetID = __('overview.ov_debris_from');
					} elseif ($TargetType == 3) {
						$TargetID = __('overview.ov_moon_from');
					} elseif ($TargetType == 5) {
						$TargetID = ' с военной базы ';
					}
				} else {
					$TargetID = __('overview.ov_explo_from');
				}

				$TargetID .= $TargetPlanet . " ";
			}

			$TargetID .= $FleetRow->getTargetAdressLink($FleetPrefix . $FleetStyle[$MissionType]);
		}

		if ($Owner == true) {
			$EventString = __('overview.ov_une');
			$EventString .= $FleetContent;
		} else {
			$EventString = ($FleetRow->group_id != 0) ? 'Союзный ' : __('overview.ov_une_hostile');
			$EventString .= $FleetContent;
			$EventString .= __('overview.ov_hostile');

			$FleetRow->username = DB::selectOne("SELECT `username` FROM users WHERE `id` = '" . $FleetRow->owner . "'")->username;

			$EventString .= Helpers::BuildHostileFleetPlayerLink($FleetRow);
		}

		if ($Status == 0) {
			$Time = $FleetRow->start_time;
			$EventString .= __('overview.ov_vennant');
			$EventString .= $StartID;
			$EventString .= __('overview.ov_atteint');
			$EventString .= $TargetID;
			$EventString .= __('overview.ov_mission');
		} elseif ($Status == 1) {
			$Time = $FleetRow->end_stay;
			$EventString .= __('overview.ov_vennant');
			$EventString .= $StartID;

			if ($MissionType == 5) {
				$EventString .= ' защищает ';
			} else {
				$EventString .= __('overview.ov_explo_stay');
			}

			$EventString .= $TargetID;
			$EventString .= __('overview.ov_explo_mission');
		} else {
			$Time = $FleetRow->end_time;
			$EventString .= __('overview.ov_rentrant');
			$EventString .= $TargetID;
			$EventString .= $StartID;
			$EventString .= __('overview.ov_mission');
		}

		$EventString .= $FleetCapacity;

		$bloc['id'] = (int) $FleetRow->id;
		$bloc['status'] = $FleetStatus[$Status];
		$bloc['prefix'] = $FleetPrefix;
		$bloc['mission'] = $FleetStyle[$MissionType];
		$bloc['date'] = Game::datezone("H:i:s", $Time);
		$bloc['time'] = (int) $Time;
		$bloc['text'] = $EventString;

		return $bloc;
	}

	public function delete(Request $request)
	{
		if ($request->isMethod('post') && $request->post('id') && $request->post('id', 0) == $this->user->planet_current) {
			if ($this->user->id != $this->planet->id_owner) {
				throw new RedirectException("Удалить планету может только владелец", '/overview/rename/');
			}

			if ($this->user->planet_id == $this->user->planet_current) {
				throw new RedirectException(__('overview.deletemessage_wrong'), '/overview/rename/');
			}

			if (md5(trim($request->post('pw'))) != $request->post('password')) {
				throw new RedirectException(__('overview.deletemessage_fail'), '/overview/delete/');
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
				throw new RedirectException('Нельзя удалять планету если с/на неё летит флот', '/overview/rename/');
			}

			$destruyed = time() + 60 * 60 * 24;

			$this->planet->destruyed = $destruyed;
			$this->planet->id_owner = 0;
			$this->planet->update();

			$this->user->planet_current = $this->user->planet_id;
			$this->user->update();

			if ($this->planet->parent_planet != 0) {
				Models\Planet::query()
					->where('id', $this->planet->parent_planet)
					->update([
						'destruyed' => $destruyed,
						'id_owner' => 0
					]);

				Models\Queue::query()
					->where('planet_id', $this->planet->parent_planet)
					->delete();
			}

			Models\Queue::query()
				->where('planet_id', $this->planet->id)
				->delete();

			if (Session::has('fleet_shortcut')) {
				Session::remove('fleet_shortcut');
			}

			Cache::forget('app::planetlist_' . $this->user->id);

			throw new RedirectException(__('overview.deletemessage_ok'), '/overview/');
		}

		$parse['number_1'] 		= mt_rand(1, 100);
		$parse['number_2'] 		= mt_rand(1, 100);
		$parse['number_3'] 		= mt_rand(1, 100);
		$parse['number_check'] 	= md5($parse['number_1'] + $parse['number_2'] * $parse['number_3']);

		$parse['id'] = $this->planet->id;
		$parse['galaxy'] = $this->planet->galaxy;
		$parse['system'] = $this->planet->system;
		$parse['planet'] = $this->planet->planet;

		$this->setTitle('Покинуть колонию');
		$this->showTopPanel(false);

		return $parse;
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
			if (strpos($this->planet->image, $type) !== false) {
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

				if (Session::has('fleet_shortcut')) {
					Session::remove('fleet_shortcut');
				}

				throw new RedirectException('Название планеты изменено', '/overview/');
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

				throw new RedirectException('Картинка планеты изменена', '/overview/');
			}
		}

		$parse['planet_name'] = $this->planet->name;

		$this->setTitle('Переименовать планету');
		$this->showTopPanel(false);

		return $parse;
	}

	public function bonus()
	{
		if ($this->user->bonus > time()) {
			throw new ErrorException('Вы не можете получить ежедневный бонус в данное время');
		}

		$multi = ($this->user->bonus_multi < 50) ? ($this->user->bonus_multi + 1) : 50;

		if ($this->user->bonus < (time() - 86400)) {
			$multi = 1;
		}

		$add = $multi * 500 * Game::getSpeed('mine');

		$this->planet->metal += $add;
		$this->planet->crystal += $add;
		$this->planet->deuterium += $add;
		$this->planet->update();

		$this->user->bonus = time() + 86400;
		$this->user->bonus_multi = $multi;

		if ($this->user->bonus_multi > 1) {
			$this->user->credits++;
		}

		$this->user->update();

		throw new RedirectException('Спасибо за поддержку!<br>Вы получили в качестве бонуса по <b>' . $add . '</b> Металла, Кристаллов и Дейтерия' . ($this->user->bonus_multi > 1 ? ', а также 1 кредит.' : '') . '', '/overview/');
	}

	public function index(Request $request)
	{
		if ($request->has('bonus')) {
			$this->bonus();
		}

		$parse = [];

		$XpMinierUp = pow($this->user->lvl_minier, 3);
		$XpRaidUp = pow($this->user->lvl_raid, 2);

		$fleets = Models\Fleet::query()
			->where('owner', $this->user->id)
			->orWhere('target_owner', $this->user->id)
			->get();

		$Record = 0;
		$fpage = [];
		$aks = [];

		foreach ($fleets as $FleetRow) {
			$Record++;

			if ($FleetRow->owner == $this->user->id) {
				if ($FleetRow->start_time > time()) {
					$fpage[$FleetRow->start_time][$FleetRow->id] = $this->buildFleetEventTable($FleetRow, 0, true);
				}

				if ($FleetRow->end_stay > time()) {
					$fpage[$FleetRow->end_stay][$FleetRow->id] = $this->buildFleetEventTable($FleetRow, 1, true);
				}

				if (!($FleetRow->mission == 7 && $FleetRow->mess == 0)) {
					if (($FleetRow->end_time > time() and $FleetRow->mission != 4) or ($FleetRow->mess == 1 and $FleetRow->mission == 4)) {
						$fpage[$FleetRow->end_time][$FleetRow->id] = $this->buildFleetEventTable($FleetRow, 2, true);
					}
				}

				if ($FleetRow->group_id != 0 && !in_array($FleetRow->group_id, $aks)) {
					$AKSFleets = Models\Fleet::query()
						->where('group_id', $FleetRow->group_id)
						->where('owner', '!=', $this->user->id)
						->where('mess', 0)
						->get();

					foreach ($AKSFleets as $AKFleet) {
						$Record++;
						$fpage[$FleetRow->start_time][$AKFleet->id] = $this->buildFleetEventTable($AKFleet, 0, false);
					}

					$aks[] = $FleetRow->group_id;
				}
			} elseif ($FleetRow->mission != 8) {
				$Record++;

				if ($FleetRow->start_time > time()) {
					$fpage[$FleetRow->start_time][$FleetRow->id] = $this->buildFleetEventTable($FleetRow, 0, false);
				}
				if ($FleetRow->mission == 5 && $FleetRow->end_stay > time()) {
					$fpage[$FleetRow->end_stay][$FleetRow->id] = $this->buildFleetEventTable($FleetRow, 1, false);
				}
			}
		}

		$parse['moon'] 	= false;

		if ($this->planet->parent_planet != 0 && $this->planet->planet_type != 3 && $this->planet->id) {
			$lune = Cache::remember('app::lune_' . $this->planet->parent_planet, 300, function () {
				$lune = Models\Planet::query()
					->select(['id', 'name', 'image', 'destruyed'])
					->where('id', $this->planet->parent_planet)
					->where('planet_type', 3)
					->first();

				return $lune ? $lune->toArray() : null;
			});

			if (isset($lune['id']) && !$lune['destruyed']) {
				$parse['moon'] = [
					'id' => $lune['id'],
					'name' => $lune['name'],
					'image' => $lune['image']

				];
			}
		}

		$records = Cache::remember('app::records_' . $this->user->getId(), 1800, function () {
			$records = Models\Statistic::query()
				->select(['build_points', 'tech_points', 'fleet_points', 'defs_points', 'total_points', 'total_old_rank', 'total_rank'])
				->where('stat_type', 1)
				->where('stat_code', 1)
				->where('id_owner', $this->user->getId())
				->first();

			return $records ? $records->toArray() : null;
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
			'metal' => (int) $this->planet->debris_metal,
			'crystal' => (int) $this->planet->debris_crystal,
		];

		$parse['debris_mission'] = (($this->planet->debris_metal != 0 || $this->planet->debris_crystal != 0) && $this->planet->getLevel('recycler') > 0);

		$build_list = [];
		$planetsData = [];

		$planets = Planet::query()
			->where('id_owner', $this->user->id)
			->get();

		foreach ($planets as $item) {
			$planetsData[$item->id] = $item;
		}

		$queueManager = new Queue($this->user);

		if ($queueManager->getCount($queueManager::TYPE_BUILDING)) {
			$queueArray = $queueManager->get($queueManager::TYPE_BUILDING);

			$end = [];

			foreach ($queueArray as $item) {
				if (!isset($end[$item->planet_id])) {
					$end[$item->planet_id] = $item->time;
				}

				$entity = new Planet\Entity\Building($item->object_id, $item->level - ($item->operation == $item::OPERATION_BUILD ? 1 : 0));

				$time = $entity->getTime();

				if ($item->operation == $item::OPERATION_DESTROY) {
					$time = ceil($time / 2);
				}

				$end[$item->planet_id] += $time;

				$build_list[$end[$item->planet_id]][] = [
					$end[$item->planet_id],
					$item->planet_id,
					$planetsData[$item->planet_id]->name,
					__('main.tech.' . $item->object_id) . ' (' . ($item->operation == $item::OPERATION_BUILD ? $item->level - 1 : $item->level + 1) . ' -> ' . $item->level . ')'
				];
			}
		}

		if ($queueManager->getCount($queueManager::TYPE_RESEARCH)) {
			$queueArray = $queueManager->get($queueManager::TYPE_RESEARCH);

			foreach ($queueArray as $item) {
				$build_list[$item->time_end][] = [
					(int) $item->time_end,
					$item->planet_id,
					$planetsData[$item->planet_id]->name,
					__('main.tech.' . $item->object_id) . ' (' . $this->user->getTechLevel($item->object_id) . ' -> ' . ($this->user->getTechLevel($item->object_id) + 1) . ')'
				];
			}
		}

		if ($queueManager->getCount($queueManager::TYPE_SHIPYARD)) {
			$queueArray = $queueManager->get($queueManager::TYPE_SHIPYARD);

			$end = [];

			foreach ($queueArray as $item) {
				if (!isset($end[$item->planet_id])) {
					$end[$item->planet_id] = $item->time;
				}

				$time = $item->time_end - $item->time;

				$end[$item->planet_id] += $time * $item->level;

				if ($end[$item->planet_id] < time()) {
					continue;
				}

				$build_list[$end[$item->planet_id]][] = [
					$end[$item->planet_id],
					$item->planet_id,
					$planetsData[$item->planet_id]->name,
					__('main.tech.' . $item->object_id) . ' (' . $item->level . ')'
				];
			}
		}

		$parse['build_list'] = [];

		if (count($build_list) > 0) {
			$parse['build_list'] = [];
			ksort($build_list);

			foreach ($build_list as $planet) {
				foreach ($planet as $text) {
					$parse['build_list'][] = $text;
				}
			}
		}

		$parse['case_pourcentage'] = floor($this->planet->field_current / $this->planet->getMaxFields() * 100);
		$parse['case_pourcentage'] = min($parse['case_pourcentage'], 100);

		$parse['lvl'] = [
			'mine' => [
				'p' => (int) $this->user->xpminier,
				'l' => (int) $this->user->lvl_minier,
				'u' => (int) $XpMinierUp,
			],
			'raid' => [
				'p' => (int) $this->user->xpraid,
				'l' => (int) $this->user->lvl_raid,
				'u' => (int) $XpRaidUp
			]
		];

		$parse['links'] = (int) $this->user->links;
		$parse['refers'] = (int) $this->user->refers;
		$parse['noob'] = config('game.noob', 0);

		$parse['raids'] = [
			'win' => (int) $this->user->raids_win,
			'lost' => (int) $this->user->raids_lose,
			'total' => (int) $this->user->raids
		];

		$parse['bonus'] = $this->user->bonus < time();

		if ($parse['bonus']) {
			$bonus = $this->user->bonus_multi + 1;

			if ($bonus > 50) {
				$bonus = 50;
			}

			if ($this->user->bonus < (time() - 86400)) {
				$bonus = 1;
			}

			$parse['bonus_count'] = $bonus * 500 * Game::getSpeed('mine');
		}

		$parse['chat'] = [];

		if (Helpers::isMobile()) {
			$chatCached = Cache::remember(config('chat.cache'), 86400, function () {
				$messages = DB::select("SELECT c.*, u.username FROM log_chat c LEFT JOIN users u ON u.id = c.user WHERE 1 = 1 ORDER BY c.time DESC LIMIT 20");

				$chat = [];

				foreach ($messages as $message) {
					if (preg_match_all("/приватно [(.*?)]/u", $message->text, $private)) {
						$message->text = preg_replace("/приватно [(.*?)]/u", '', $message->text);
					}

					if (preg_match_all("/для [(.*?)]/u", $message->text, $to)) {
						$message->text = preg_replace("/для [(.*?)]/u", '', $message->text);

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

					$message->text = trim($message->text);

					$chat[] = [$message->id, $message->time, $message->username, $to[1], $isPrivate, $message->text, 0];
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

		$this->setTitle('Обзор');

		return $parse;
	}
}
