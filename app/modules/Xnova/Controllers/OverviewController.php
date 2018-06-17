<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Cache\Backend\Memcache;
use Xnova\Building;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Fleet;
use Xnova\Helpers;
use Friday\Core\Lang;
use Xnova\Models\Planet;
use Xnova\Queue;
use Xnova\Models\Fleet as FleetModel;
use Xnova\Controller;
use Phalcon\Cache\Frontend\None as FrontendCache;
use Xnova\Request;
use Xnova\Vars;

/**
 * @RoutePrefix("/overview")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class OverviewController extends Controller
{
	public function initialize()
	{
		parent::initialize();

		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('overview', 'xnova');

		$this->user->loadPlanet();
	}

	private function BuildFleetEventTable (FleetModel $FleetRow, $Status, $Owner)
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

		$FleetContent 	= Fleet::CreateFleetPopupedFleetLink($FleetRow, _getText('ov_fleet'), $FleetPrefix . $FleetStyle[$MissionType], $this->user);
		$FleetCapacity 	= Fleet::CreateFleetPopupedMissionLink($FleetRow, _getText('type_mission', $MissionType), $FleetPrefix . $FleetStyle[$MissionType]);

		$StartPlanet 	= $FleetRow->owner_name;
		$StartType 		= $FleetRow->start_type;
		$TargetPlanet 	= $FleetRow->target_owner_name;
		$TargetType 	= $FleetRow->end_type;

		$StartID  = '';
		$TargetID = '';

		if ($Status != 2)
		{
			if ($StartPlanet == '')
				$StartID = ' с координат ';
			else
			{
				if ($StartType == 1)
					$StartID = _getText('ov_planet_to');
				elseif ($StartType == 3)
					$StartID = _getText('ov_moon_to');
				elseif ($StartType == 5)
					$StartID = ' с военной базы ';

				$StartID .= $StartPlanet . " ";
			}

			$StartID .= $FleetRow->getStartAdressLink($FleetPrefix . $FleetStyle[$MissionType]);

			if ($TargetPlanet == '')
				$TargetID = ' координаты ';
			else
			{
				if ($MissionType != 15 && $MissionType != 5)
				{
					if ($TargetType == 1)
						$TargetID = _getText('ov_planet_to_target');
					elseif ($TargetType == 2)
						$TargetID = _getText('ov_debris_to_target');
					elseif ($TargetType == 3)
						$TargetID = _getText('ov_moon_to_target');
					elseif ($TargetType == 5)
						$TargetID = ' военной базе ';
				}
				else
					$TargetID = _getText('ov_explo_to_target');

				$TargetID .= $TargetPlanet . " ";
			}

			$TargetID .= $FleetRow->getTargetAdressLink($FleetPrefix . $FleetStyle[$MissionType]);
		}
		else
		{
			if ($StartPlanet == '')
				$StartID = ' на координаты ';
			else
			{
				if ($StartType == 1)
					$StartID = _getText('ov_back_planet');
				elseif ($StartType == 3)
					$StartID = _getText('ov_back_moon');

				$StartID .= $StartPlanet . " ";
			}

			$StartID .= $FleetRow->getStartAdressLink($FleetPrefix . $FleetStyle[$MissionType]);

			if ($TargetPlanet == '')
				$TargetID = ' с координат ';
			else
			{
				if ($MissionType != 15)
				{
					if ($TargetType == 1)
						$TargetID = _getText('ov_planet_from');
					elseif ($TargetType == 2)
						$TargetID = _getText('ov_debris_from');
					elseif ($TargetType == 3)
						$TargetID = _getText('ov_moon_from');
					elseif ($TargetType == 5)
						$TargetID = ' с военной базы ';
				}
				else
					$TargetID = _getText('ov_explo_from');

				$TargetID .= $TargetPlanet . " ";
			}

			$TargetID .= $FleetRow->getTargetAdressLink($FleetPrefix . $FleetStyle[$MissionType]);
		}

		if ($Owner == true)
		{
			$EventString = _getText('ov_une');
			$EventString .= $FleetContent;
		}
		else
		{
			$EventString = ($FleetRow->group_id != 0) ? 'Союзный ' : _getText('ov_une_hostile');
			$EventString .= $FleetContent;
			$EventString .= _getText('ov_hostile');

			$FleetRow->username = $this->db->fetchColumn("SELECT `username` FROM game_users WHERE `id` = '" . $FleetRow->owner . "'");

			$EventString .= Helpers::BuildHostileFleetPlayerLink($FleetRow);
		}

		if ($Status == 0)
		{
			$Time = $FleetRow->start_time;
			$EventString .= _getText('ov_vennant');
			$EventString .= $StartID;
			$EventString .= _getText('ov_atteint');
			$EventString .= $TargetID;
			$EventString .= _getText('ov_mission');
		}
		elseif ($Status == 1)
		{
			$Time = $FleetRow->end_stay;
			$EventString .= _getText('ov_vennant');
			$EventString .= $StartID;

			if ($MissionType == 5)
				$EventString .= ' защищает ';
			else
				$EventString .= _getText('ov_explo_stay');

			$EventString .= $TargetID;
			$EventString .= _getText('ov_explo_mission');
		}
		else
		{
			$Time = $FleetRow->end_time;
			$EventString .= _getText('ov_rentrant');
			$EventString .= $TargetID;
			$EventString .= $StartID;
			$EventString .= _getText('ov_mission');
		}

		$EventString .= $FleetCapacity;

		$bloc['id'] = (int) $FleetRow->id;
		$bloc['status'] = $FleetStatus[$Status];
		$bloc['prefix'] = $FleetPrefix;
		$bloc['mission'] = $FleetStyle[$MissionType];
		$bloc['date'] = $this->game->datezone("H:i:s", $Time);
		$bloc['time'] = $Time;
		$bloc['text'] = $EventString;

		return $bloc;
	}

	public function deleteAction ()
	{
		if ($this->request->isPost() && $this->request->hasPost('id') && $this->request->getPost('id', 'int', 0) == $this->user->planet_current)
		{
			if ($this->user->id != $this->planet->id_owner)
				throw new RedirectException("Удалить планету может только владелец", _getText('colony_abandon'), '/overview/rename/');

			if ($this->user->planet_id == $this->user->planet_current)
				throw new RedirectException(_getText('deletemessage_wrong'), _getText('colony_abandon'), '/overview/rename/');

			if (md5(trim($this->request->getPost('pw'))) != $this->request->getPost('password'))
				throw new RedirectException(_getText('deletemessage_fail'), _getText('colony_abandon'), '/overview/delete/');

			$checkFleets = FleetModel::count(['(start_galaxy = :galaxy: AND start_system = :system: AND start_planet = :planet: AND start_type = :type:) OR (end_galaxy = :galaxy: AND end_system = :system: AND end_planet = :planet: AND end_type = :type:)', 'bind' => ['galaxy' => $this->planet->galaxy, 'system' => $this->planet->system, 'planet' => $this->planet->planet, 'type' => $this->planet->planet_type]]);

			if ($checkFleets > 0)
				throw new RedirectException('Нельзя удалять планету если с/на неё летит флот', _getText('colony_abandon'), '/overview/rename/');

			$destruyed = time() + 60 * 60 * 24;

			$this->planet->destruyed = $destruyed;
			$this->planet->id_owner = 0;
			$this->planet->update();

			$this->user->planet_current = $this->user->planet_id;
			$this->user->update();

			if ($this->planet->parent_planet != 0)
			{
				$this->db->updateAsDict('game_planets', [
					'destruyed' => $destruyed,
					'id_owner' => 0
				], "id = '".$this->planet->parent_planet."'");

				$queue = \Xnova\Models\Queue::find([
					'conditions' => 'planet_id = :planet:',
					'bind' => [
						'planet' => $this->planet->parent_planet
					]
				]);

				foreach ($queue as $item)
					$item->delete();
			}

			$queue = \Xnova\Models\Queue::find([
				'conditions' => 'planet_id = :planet:',
				'bind' => [
					'planet' => $this->planet->id
				]
			]);

			foreach ($queue as $item)
				$item->delete();

			if ($this->session->has('fleet_shortcut'))
				$this->session->remove('fleet_shortcut');

			$this->cache->delete('app::planetlist_'.$this->user->id);

			throw new RedirectException(_getText('deletemessage_ok'), _getText('colony_abandon'), '/overview/');
		}

		$parse['number_1'] 		= mt_rand(1, 100);
		$parse['number_2'] 		= mt_rand(1, 100);
		$parse['number_3'] 		= mt_rand(1, 100);
		$parse['number_check'] 	= $parse['number_1'] + $parse['number_2'] * $parse['number_3'];

		$parse['id'] = $this->planet->id;
		$parse['galaxy'] = $this->planet->galaxy;
		$parse['system'] = $this->planet->system;
		$parse['planet'] = $this->planet->planet;

		$this->view->setVar('parse', $parse);
		$this->tag->setTitle('Покинуть колонию');
		$this->showTopPanel(false);
	}

	public function renameAction ()
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

		foreach ($parse['images'] AS $type => $max)
		{
			if (strpos($this->planet->image, $type) !== false)
				$parse['type'] = $type;
		}

		if ($this->request->hasPost('action') && $this->request->getPost('action') == _getText('namer'))
		{
			$name = strip_tags(trim($this->request->getPost('newname', 'string', '')));

			if ($name != '')
			{
				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $name))
					throw new RedirectException('Введённое имя содержит недопустимые символы', 'Ошибка', $this->url->get('overview/rename/'), 5);

				if (mb_strlen($name, 'UTF-8') <= 1 || mb_strlen($name, 'UTF-8') >= 20)
					throw new RedirectException('Введённо слишком длинное или короткое имя планеты', 'Ошибка', $this->url->get('overview/rename/'), 5);

				$this->planet->name = $name;
				$this->planet->update();

				if ($this->session->has('fleet_shortcut'))
					$this->session->remove('fleet_shortcut');
			}
		}
		elseif ($this->request->hasPost('action') && $this->request->hasPost('image'))
		{
			if ($this->user->credits < 1)
				throw new RedirectException('Недостаточно кредитов', 'Ошибка', '/overview/rename/');

			$image = (int) $this->request->getPost('image', 'int', 0);

			if ($image <= 0 || $image > $parse['images'][$parse['type']])
				throw new RedirectException('Недостаточно читерских навыков', 'Ошибка', '/overview/rename/');

			$this->planet->image = $parse['type'].'planet'.($image < 10 ? '0' : '').$image;
			$this->planet->update();

			$this->user->credits--;
			$this->user->update();

			$this->response->redirect('overview/');
		}

		$parse['planet_name'] = $this->planet->name;

		$this->view->setVar('parse', $parse);
		$this->tag->setTitle('Переименовать планету');
		$this->showTopPanel(false);
	}

	public function bonusAction ()
	{
		if ($this->user->bonus < time())
		{
			$multi = ($this->user->bonus_multi < 50) ? ($this->user->bonus_multi + 1) : 50;

			if ($this->user->bonus < (time() - 86400))
				$multi = 1;

			$add = $multi * 500 * $this->game->getSpeed('mine');

			$this->planet->metal += $add;
			$this->planet->crystal += $add;
			$this->planet->deuterium += $add;
			$this->planet->update();

			$this->user->bonus = time() + 86400;
			$this->user->bonus_multi = $multi;

			if ($this->user->bonus_multi > 1)
				$this->user->credits++;

			$this->user->update();

			throw new RedirectException('Спасибо за поддержку!<br>Вы получили в качестве бонуса по <b>' . $add . '</b> Металла, Кристаллов и Дейтерия'.($this->user->bonus_multi > 1 ? ', а также 1 кредит.' : '').'', 'Ежедневный бонус', '/overview/', 2);
		}
		else
			throw new ErrorException('Ошибочка вышла, сорри :(');
	}

	public function indexAction ()
	{
		$parse = [];

		$XpMinierUp = pow($this->user->lvl_minier, 3);
		$XpRaidUp = pow($this->user->lvl_raid, 2);

		$fleets = FleetModel::find(['conditions' => 'owner = :user: OR target_owner = :user:', 'bind' => ['user' => $this->user->id]]);

		$Record = 0;
		$fpage = [];
		$aks = [];

		foreach ($fleets AS $FleetRow)
		{
			$Record++;

			if ($FleetRow->owner == $this->user->id)
			{
				if ($FleetRow->start_time > time())
					$fpage[$FleetRow->start_time][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 0, true);

				if ($FleetRow->end_stay > time())
					$fpage[$FleetRow->end_stay][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 1, true);

				if (!($FleetRow->mission == 7 && $FleetRow->mess == 0))
				{
					if (($FleetRow->end_time > time() AND $FleetRow->mission != 4) OR ($FleetRow->mess == 1 AND $FleetRow->mission == 4))
						$fpage[$FleetRow->end_time][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 2, true);
				}

				if ($FleetRow->group_id != 0 && !in_array($FleetRow->group_id, $aks))
				{
					$AKSFleets = FleetModel::find(['conditions' => 'group_id = :group: AND owner != :user: AND mess = 0', 'bind' => ['group' => $FleetRow->group_id, 'user' => $this->user->id]]);

					foreach ($AKSFleets as $AKFleet)
					{
						$Record++;
						$fpage[$FleetRow->start_time][$AKFleet->id] = $this->BuildFleetEventTable($AKFleet, 0, false);
					}

					$aks[] = $FleetRow->group_id;
				}
			}
			elseif ($FleetRow->mission != 8)
			{
				$Record++;

				if ($FleetRow->start_time > time())
					$fpage[$FleetRow->start_time][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 0, false);
				if ($FleetRow->mission == 5 && $FleetRow->end_stay > time())
					$fpage[$FleetRow->end_stay][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 1, false);
			}
		}

		$parse['moon'] 	= false;

		if ($this->planet->parent_planet != 0 && $this->planet->planet_type != 3 && $this->planet->id)
		{
			$lune = $this->cache->get('app::lune_'.$this->planet->parent_planet);

			if ($lune === null)
			{
				$lune = Planet::findFirst(['columns' => 'id, name, image, destruyed', 'conditions' => 'id = ?0 AND planet_type = 3', 'bind' => [$this->planet->parent_planet]]);

				if ($lune)
					$this->cache->save('app::lune_'.$this->planet->parent_planet, $lune->toArray(), 300);
			}

			if (isset($lune['id']) && !$lune['destruyed'])
			{
				$parse['moon'] = [
					'id' => $lune['id'],
					'name' => $lune['name'],
					'image' => $lune['image']

				];
			}
		}

		$parse['planet'] = [
			'type' => _getText('type_planet', $this->planet->planet_type),
			'name' => $this->planet->name,
			'image' => $this->planet->image,
			'diameter' => (int) $this->planet->diameter,
			'field_used' => (int) $this->planet->field_current,
			'field_max' => (int) $this->planet->getMaxFields(),
			'temp_min' => (int) $this->planet->temp_min,
			'temp_max' => (int) $this->planet->temp_max,
			'galaxy' => (int) $this->planet->galaxy,
			'system' => (int) $this->planet->system,
			'planet' => (int) $this->planet->planet
		];

		$records = $this->cache->get('app::records_'.$this->user->getId());

		if ($records === null)
		{
			$records = $this->db->query("SELECT `build_points`, `tech_points`, `fleet_points`, `defs_points`, `total_points`, `total_old_rank`, `total_rank` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $this->user->getId() . "'")->fetch();

			if (!is_array($records))
				$records = [];

			$this->cache->save('app::records_'.$this->user->getId().'', $records, 1800);
		}

		$parse['points'] = [
			'build' => 0,
			'tech' => 0,
			'fleet' => 0,
			'defs' => 0,
			'total' => 0,
			'place' => 0,
			'diff' => 0
		];

		if (count($records))
		{
			if (!$records['total_old_rank'])
				$records['total_old_rank'] = $records['total_rank'];

			$parse['points']['build'] = (int) $records['build_points'];
			$parse['points']['tech'] = (int) $records['tech_points'];
			$parse['points']['fleet'] = (int) $records['fleet_points'];
			$parse['points']['defs'] = (int) $records['defs_points'];
			$parse['points']['total'] = (int) $records['total_points'];
			$parse['points']['place'] = (int) $records['total_rank'];
			$parse['points']['diff'] = (int) $records['total_old_rank'] - (int) $records['total_rank'];
		}

		$flotten = [];

		if (count($fpage) > 0)
		{
			ksort($fpage);
			foreach ($fpage as $content)
			{
				foreach ($content AS $text)
				{
					$flotten[] = $text;
				}
			}
		}

		$parse['fleets'] = $flotten;

		$parse['debris'] = [
			'metal' => (int) $this->planet->debris_metal,
			'crystal' => (int) $this->planet->debris_crystal,
		];

		$parse['debris_mission'] = (($this->planet->debris_metal != 0 || $this->planet->debris_crystal != 0) && $this->planet->getUnitCount('recycler') > 0);

		$build_list = [];
		$planetsData = [];

		$planets = Planet::find([
			'conditions' => 'id_owner = :user:',
			'bind' => [
				'user' => $this->user->id
			]
		]);

		foreach ($planets as $item)
			$planetsData[$item->id] = $item;

		$queueManager = new Queue($this->user);

		if ($queueManager->getCount($queueManager::TYPE_BUILDING))
		{
			$queueArray = $queueManager->get($queueManager::TYPE_BUILDING);

			$end = [];

			foreach ($queueArray AS $item)
			{
				if (!isset($end[$item->planet_id]))
					$end[$item->planet_id] = $item->time;

				$time = Building::getBuildingTime($this->user, $planetsData[$item->planet_id], $item->object_id, $item->level - ($item->operation == $item::OPERATION_BUILD ? 1 : 0));

				if ($item->operation == $item::OPERATION_DESTROY)
					$time = ceil($time / 2);

				$end[$item->planet_id] += $time;

				$build_list[$end[$item->planet_id]][] = [
					$end[$item->planet_id],
					$item->planet_id,
					$planetsData[$item->planet_id]->name,
					_getText('tech', $item->object_id).' ('.($item->operation == $item::OPERATION_BUILD ? $item->level - 1 : $item->level + 1).' -> '.$item->level.')'
				];
			}
		}

		if ($queueManager->getCount($queueManager::TYPE_RESEARCH))
		{
			$queueArray = $queueManager->get($queueManager::TYPE_RESEARCH);

			foreach ($queueArray AS $item)
			{
				$build_list[$item->time_end][] = [
					$item->time_end,
					$item->planet_id,
					$planetsData[$item->planet_id]->name,
					_getText('tech', $item->object_id).' ('.$this->user->getTechLevel($item->object_id).' -> '.($this->user->getTechLevel($item->object_id) + 1).')'
				];
			}
		}

		if ($queueManager->getCount($queueManager::TYPE_SHIPYARD))
		{
			$queueArray = $queueManager->get($queueManager::TYPE_SHIPYARD);

			$end = [];

			foreach ($queueArray AS $item)
			{
				if (!isset($end[$item->planet_id]))
					$end[$item->planet_id] = $item->time;

				$time = $item->time_end - $item->time;

				$end[$item->planet_id] += $time * $item->level;

				if ($end[$item->planet_id] < time())
					continue;

				$build_list[$end[$item->planet_id]][] = [
					$end[$item->planet_id],
					$item->planet_id,
					$planetsData[$item->planet_id]->name,
					_getText('tech', $item->object_id).' ('.$item->level.')'
				];
			}
		}

		$parse['build_list'] = [];

		if (count($build_list) > 0)
		{
			$parse['build_list'] = [];
			ksort($build_list);

			foreach ($build_list as $planet)
			{
				foreach ($planet AS $text)
				{
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
		$parse['noob'] = $this->config->game->get('noob', 0);

		$parse['raids'] = [
			'win' => (int) $this->user->raids_win,
			'lost' => (int) $this->user->raids_lose,
			'total' => (int) $this->user->raids
		];

		$parse['bonus'] = $this->user->bonus < time();

		if ($parse['bonus'])
		{
			$bonus = $this->user->bonus_multi + 1;

			if ($bonus > 50)
				$bonus = 50;

			if ($this->user->bonus < (time() - 86400))
				$bonus = 1;

			$parse['bonus_count'] = $bonus * 500 * $this->game->getSpeed('mine');
		}

		$parse['officiers'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $officier)
		{
			$parse['officiers'][] = [
				'id' => (int) $officier,
				'time' => (int) $this->user->{Vars::getName($officier)},
				'name' => _getText('tech', $officier)
			];
		}

		$parse['chat'] = [];

		if (isMobile())
		{
			$memcache = new Memcache(new FrontendCache(), [
				'host' => $this->config->memcache->host,
				'port' => $this->config->memcache->port,
				'persistent' => true
			]);

			$chatCached = $memcache->get($this->config->chat->cache);

			if (is_string($chatCached))
				$chat = json_decode($chatCached, true);
			else
				$chat = null;

			if (!is_array($chat) || !count($chat))
			{
				$messages = $this->db->query("SELECT c.*, u.username FROM game_log_chat c LEFT JOIN game_users u ON u.id = c.user WHERE 1 = 1 ORDER BY c.time DESC LIMIT 20");

				$chat = [];

				while ($message = $messages->fetch())
				{
					if (preg_match_all("/приватно \[(.*?)\]/u", $message['text'], $private))
					{
						$message['text'] = preg_replace("/приватно \[(.*?)\]/u", '', $message['text']);
					}

					if (preg_match_all("/для \[(.*?)\]/u", $message['text'], $to))
					{
						$message['text'] = preg_replace("/для \[(.*?)\]/u", '', $message['text']);

						if (isset($private[1]) && count($private[1]) > 0)
						{
							$private[1] = array_merge($private[1], $to[1]);
							unset($to[1]);
						}
					}

					if (!isset($to[1]))
						$to[1] = [];

					$isPrivate = false;

					if (isset($private['1']) && count($private[1]) > 0)
					{
						$to[1] = $private[1];
						$isPrivate = true;
					}

					$message['text'] = trim($message['text']);

					$chat[] = [$message['id'], $message['time'], $message['username'], $to[1], $isPrivate, $message['text'], 0];
				}

				$chat = array_reverse($chat);

				$memcache->save($this->config->chat->cache, json_encode($chat), 86400);
			}

			if (is_array($chat) && count($chat))
			{
				$chat = array_reverse($chat);

				$i = 0;

				foreach ($chat AS $message)
				{
					if ($message[4] != false)
						continue;

					if ($i >= 5)
						break;

					$t = explode(' ', $message[5]);

					foreach ($t AS $j => $w)
					{
						if (mb_strlen($w, 'UTF-8') > 30)
						{
							$w = str_split(iconv('utf-8', 'windows-1251', $w), 30);

							$t[$j] = iconv('windows-1251', 'utf-8', implode(' ', $w));
						}
					}

					$message[5] = implode(' ', $t);

					$parse['chat'][] = [
						'time' => $message[1],
						'message' => '<span class="title"><span class="to">'.$message[2].'</span> написал'.(count($message[3]) ? ' <span class="to">'.implode(', ', $message[3]).'</span>' : '').'</span>: '.$message[5].''
					];

					$i++;
				}
			}
		}

		$showMessage = false;

		foreach (Vars::getResources() AS $res)
		{
			if ($this->planet->getBuildLevel($res.'_mine') && !$this->planet->getBuild($res.'_mine')['power'])
				$showMessage = true;
		}

		if ($showMessage)
			$this->view->setVar('globalMessage', '<span class="negative">Одна из шахт находится в выключенном состоянии. Зайдите в меню "<a href="'.$this->url->get('resources/').'">Сырьё</a>" и восстановите производство.</span>');

		Request::addData('page', $parse);

		$this->tag->setTitle('Обзор');
	}
}