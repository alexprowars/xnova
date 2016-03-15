<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Fleet;
use App\Helpers;
use App\Lang;
use App\Models\Planet;
use App\Queue;
use App\Models\Fleet as FleetModel;
use Phalcon\Mvc\Model\Resultset;

class OverviewController extends ApplicationController
{
	public function initialize()
	{
		parent::initialize();

		Lang::includeLang('overview');

		$this->user->loadPlanet();
	}

	private function BuildFleetEventTable (FleetModel $FleetRow, $Status, $Owner, $Label, $Record)
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
			$Rest = $Time - time();
			$EventString .= _getText('ov_vennant');
			$EventString .= $StartID;
			$EventString .= _getText('ov_atteint');
			$EventString .= $TargetID;
			$EventString .= _getText('ov_mission');
		}
		elseif ($Status == 1)
		{
			$Time = $FleetRow->end_stay;
			$Rest = $Time - time();
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
			$Rest = $Time - time();
			$EventString .= _getText('ov_rentrant');
			$EventString .= $TargetID;
			$EventString .= $StartID;
			$EventString .= _getText('ov_mission');
		}

		$EventString .= $FleetCapacity;

		$bloc['fleet_status'] = $FleetStatus[$Status];
		$bloc['fleet_prefix'] = $FleetPrefix;
		$bloc['fleet_style'] = $FleetStyle[$MissionType];
		$bloc['fleet_order'] = $Label . $Record;
		$bloc['fleet_time'] = $this->game->datezone("H:i:s", $Time);
		$bloc['fleet_count_time'] = Helpers::pretty_time($Rest, ':');
		$bloc['fleet_descr'] = $EventString;
		$bloc['fleet_javas'] = Helpers::InsertJavaScriptChronoApplet($Label, $Record, $Rest);

		return $bloc;
	}

	public function deleteAction ()
	{
		if ($this->request->isPost() && $this->request->hasPost('id') && $this->request->getPost('id', 'int', 0) == $this->user->planet_current)
		{
			if ($this->user->id != $this->planet->id_owner)
				$this->message("Удалить планету может только владелец", _getText('colony_abandon'), '/overview/rename/');
			elseif (md5(trim($_POST['pw'])) == $_POST["password"] && $this->user->planet_id != $this->user->planet_current)
			{
				$checkFleets = FleetModel::count(['(start_galaxy = :galaxy: AND start_system = :system: AND start_planet = :planet: AND start_type = :type:) OR (end_galaxy = :galaxy: AND end_system = :system: AND end_planet = :planet: AND end_type = :type:)', 'bind' => ['galaxy' => $this->planet->galaxy, 'system' => $this->planet->system, 'planet' => $this->planet->planet, 'type' => $this->planet->planet_type]]);

				if ($checkFleets > 0)
					$this->message('Нельзя удалять планету если с/на неё летит флот', _getText('colony_abandon'), '/overview/rename/');
				else
				{
					$destruyed = time() + 60 * 60 * 24;

					$this->planet->destruyed = $destruyed;
					$this->planet->id_owner = 0;
					$this->planet->update();

					$this->user->planet_current = $this->user->planet_id;
					$this->user->update();

					if ($this->planet->parent_planet != 0)
						$this->db->updateAsDict('game_planets', ['destruyed' => $destruyed, 'id_owner' => 0], "id = '".$this->planet->parent_planet."'");

					if ($this->session->has('fleet_shortcut'))
						$this->session->remove('fleet_shortcut');

					$this->cache->delete('app::planetlist_'.$this->user->id);

					$this->message(_getText('deletemessage_ok'), _getText('colony_abandon'), '/overview/');
				}

			}
			elseif ($this->user->planet_id == $this->user->planet_current)
				$this->message(_getText('deletemessage_wrong'), _getText('colony_abandon'), '/overview/rename/');
			else
				$this->message(_getText('deletemessage_fail'), _getText('colony_abandon'), '/overview/delete/');
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

		if (isset($_POST['action']) && $_POST['action'] == _getText('namer'))
		{
			$newName = strip_tags(trim($this->request->getPost('newname', 'string', '')));

			if ($newName != "")
			{
				if (preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $newName))
				{
					if (mb_strlen($newName, 'UTF-8') > 1 && mb_strlen($newName, 'UTF-8') < 20)
					{
						$this->planet->name = $newName;
						$this->planet->update();

						if ($this->session->has('fleet_shortcut'))
							$this->session->remove('fleet_shortcut');
					}
					else
						$this->message('Введённо слишком длинное или короткое имя планеты', 'Ошибка', $this->url->get('overview/rename/'), 5);
				}
				else
					$this->message('Введённое имя содержит недопустимые символы', 'Ошибка', $this->url->get('overview/rename/'), 5);
			}
		}
		elseif (isset($_POST['action']) && $this->request->hasPost('image'))
		{
			if ($this->user->credits < 1)
				$this->message('Недостаточно кредитов', 'Ошибка', '/overview/rename/');

			$image = $this->request->getPost('image', 'int', 0);

			if ($image > 0 && $image <= $parse['images'][$parse['type']])
			{
				$this->planet->image = $parse['type'].'planet'.($image < 10 ? '0' : '').$image;
				$this->planet->update();

				$this->user->credits--;
				$this->user->update();

				$this->response->redirect('/overview/');
			}
			else
				$this->message('Недостаточно читерских навыков', 'Ошибка', '/overview/rename/');
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

			$this->message('Спасибо за поддержку!<br>Вы получили в качестве бонуса по <b>' . $add . '</b> Металла, Кристаллов и Дейтерия'.($this->user->bonus_multi > 1 ? ', а также 1 кредит.' : '').'', 'Ежедневный бонус', '/overview/', 2);
		}
		else
			$this->message('Ошибочка вышла, сорри :(');
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
					$fpage[$FleetRow->start_time][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 0, true, "fs", $Record);

				if ($FleetRow->end_stay > time())
					$fpage[$FleetRow->end_stay][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 1, true, "ft", $Record);

				if (!($FleetRow->mission == 7 && $FleetRow->mess == 0))
				{
					if (($FleetRow->end_time > time() AND $FleetRow->mission != 4) OR ($FleetRow->mess == 1 AND $FleetRow->mission == 4))
						$fpage[$FleetRow->end_time][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 2, true, "fe", $Record);
				}

				if ($FleetRow->group_id != 0 && !in_array($FleetRow->group_id, $aks))
				{
					$AKSFleets = FleetModel::find(['conditions' => 'group_id = :group: AND owner != :user: AND mess = 0', 'bind' => ['group' => $FleetRow->group_id, 'user' => $this->user->id]]);

					foreach ($AKSFleets as $AKFleet)
					{
						$Record++;
						$fpage[$FleetRow->start_time][$AKFleet->id] = $this->BuildFleetEventTable($AKFleet, 0, false, "fs", $Record);
					}

					$aks[] = $FleetRow->group_id;
				}

			}
			elseif ($FleetRow->mission != 8)
			{
				$Record++;

				if ($FleetRow->start_time > time())
					$fpage[$FleetRow->start_time][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 0, false, "ofs", $Record);
				if ($FleetRow->mission == 5 && $FleetRow->end_stay > time())
					$fpage[$FleetRow->end_stay][$FleetRow->id] = $this->BuildFleetEventTable($FleetRow, 1, false, "oft", $Record);
			}
		}

		$parse['moon_img'] 	= '';
		$parse['moon'] 		= '';

		if ($this->planet->parent_planet != 0 && $this->planet->planet_type != 3 && $this->planet->id)
		{
			$lune = $this->cache->get('app::lune_'.$this->planet->parent_planet);

			if ($lune === NULL)
			{
				$lune = Planet::findFirst(['columns' => 'id, name, image, destruyed', 'conditions' => 'id = ?0 AND planet_type = 3', 'bind' => [$this->planet->parent_planet]]);

				if ($lune)
					$this->cache->save('app::lune_'.$this->planet->parent_planet, $lune->toArray(), 300);
			}

			if (isset($lune['id']))
			{
				$parse['moon_img'] = "<a href=\"/overview/?chpl=" . $lune['id'] . "\" title=\"" . $lune['name'] . "\"><img src=\"/assets/images/planeten/" . $lune['image'] . ".jpg\" height=\"50\" width=\"50\"></a>";
				$parse['moon'] = ($lune['destruyed'] == 0) ? $lune['name'] : 'Фантом';
			}
		}

		if ($this->config->view->get('overviewListView', 0) == 0)
			$QryPlanets = $this->user->getPlanetListSortQuery();
		else
			$QryPlanets = '';

		$build_list = [];

		/**
		 * @var $planets \App\Models\Planet[]
		 */
		$planets = Planet::find([
			'conditions' => 'id_owner = :user: AND planet_type != :type: AND id != :id: AND queue IS NOT NULL AND queue != :queue:',
			'orders' => $QryPlanets,
			'bind' => ['user' => $this->user->id, 'type' => 3, 'id' => $this->user->planet_current, 'queue' => '[]']
		]);

		if (count($planets) > 0)
		{
			foreach ($planets as $UserPlanet)
			{
				if (!$UserPlanet->isEmptyQueue())
				{
					if (!isset($queueManager))
						$queueManager = new Queue();

					$queueManager->loadQueue($UserPlanet->queue);

					if ($queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING))
					{
						$UserPlanet->assignUser($this->user);
						$UserPlanet->updateQueueList();

						$QueueArray = $queueManager->get($queueManager::QUEUE_TYPE_BUILDING);

						foreach ($QueueArray AS $CurrBuild)
						{
							$build_list[$CurrBuild['e']][] = [$CurrBuild['e'], "<a href=\"/buildings/?chpl=" . $UserPlanet->id . "\" style=\"color:#33ff33;\">" . $UserPlanet->name . "</a>: </span><span class=\"holding colony\"> " . _getText('tech', $CurrBuild['i']) . ' (' . ($CurrBuild['l'] - 1) . ' -> ' . $CurrBuild['l'] . ')'];
						}
					}

					if ($queueManager->getCount($queueManager::QUEUE_TYPE_RESEARCH))
					{
						$QueueArray = $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

						$build_list[$QueueArray[0]['e']][] = [$QueueArray[0]['e'], "<a href=\"/buildings/research" . (($QueueArray[0]['i'] > 300) ? '_fleet' : '') . "/?chpl=" . $UserPlanet->id . "\" style=\"color:#33ff33;\">" . $UserPlanet->name . "</a>: </span><span class=\"holding colony\"> " . _getText('tech', $QueueArray[0]['i']) . ' (' . $this->user->{$this->storage->resource[$QueueArray[0]['i']]} . ' -> ' . ($this->user->{$this->storage->resource[$QueueArray[0]['i']]} + 1) . ')'];
					}
				}
			}
		}

		unset($planets);

		$parse['planet_type'] = $this->planet->planet_type;
		$parse['planet_name'] = $this->planet->name;
		$parse['planet_diameter'] = $this->planet->diameter;
		$parse['planet_field_current'] = $this->planet->field_current;
		$parse['planet_field_max'] = $this->planet->getMaxFields();
		$parse['planet_temp_min'] = $this->planet->temp_min;
		$parse['planet_temp_max'] = $this->planet->temp_max;
		$parse['galaxy_galaxy'] = $this->planet->galaxy;
		$parse['galaxy_planet'] = $this->planet->planet;
		$parse['galaxy_system'] = $this->planet->system;

		$records = $this->cache->get('app::records_'.$this->user->getId());

		if ($records === null)
		{
			$records = $this->db->query("SELECT `build_points`, `tech_points`, `fleet_points`, `defs_points`, `total_points`, `total_old_rank`, `total_rank` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $this->user->getId() . "'")->fetch();

			if (!is_array($records))
				$records = [];

			$this->cache->save('app::records_'.$this->user->getId().'', $records, 1800);
		}

		if (count($records))
		{
			$parse['user_points'] = $records['build_points'];
			$parse['player_points_tech'] = $records['tech_points'];
			$parse['total_points'] = $records['total_points'];
			$parse['user_fleet'] = $records['fleet_points'];
			$parse['user_defs'] = $records['defs_points'];

			$parse['user_rank'] = $records['total_rank'] + 0;

			if (!$records['total_old_rank'])
				$records['total_old_rank'] = $records['total_rank'];

			$parse['ile'] = $records['total_old_rank'] - $records['total_rank'];
		}
		else
		{
			$parse['user_points'] = 0;
			$parse['player_points_tech'] = 0;
			$parse['total_points'] = 0;
			$parse['user_fleet'] = 0;
			$parse['user_defs'] = 0;

			$parse['user_rank'] = 0;
			$parse['ile'] = 0;
		}

		$parse['user_username'] = $this->user->username;

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

		$parse['fleet_list'] = $flotten;

		$parse['planet_image'] = $this->planet->image;
		$parse['max_users'] = $this->config->app->users_total;

		$parse['metal_debris'] = $this->planet->debris_metal;
		$parse['crystal_debris'] = $this->planet->debris_crystal;

		$parse['get_link'] = (($this->planet->debris_metal != 0 || $this->planet->debris_crystal != 0) && $this->planet->{$this->storage->resource[209]} != 0);

		if (!$this->planet->isEmptyQueue())
		{
			if (!isset($queueManager))
				$queueManager = new Queue();

			$queueManager->loadQueue($this->planet->queue);

			if ($queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING))
			{
				$this->planet->updateQueueList();

				$BuildQueue = $queueManager->get($queueManager::QUEUE_TYPE_BUILDING);

				foreach ($BuildQueue AS $CurrBuild)
				{
					$build_list[$CurrBuild['e']][] = [$CurrBuild['e'], $this->planet->name . ": </span><span class=\"holding colony\"> " . _getText('tech', $CurrBuild['i']) . ' (' . ($CurrBuild['l'] - 1) . ' -> ' . ($CurrBuild['l']) . ')'];
				}
			}

			if ($queueManager->getCount($queueManager::QUEUE_TYPE_RESEARCH))
			{
				$QueueArray = $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

				$build_list[$QueueArray[0]['e']][] = [$QueueArray[0]['e'], $this->planet->name . ": </span><span class=\"holding colony\"> " . _getText('tech', $QueueArray[0]['i']) . ' (' . $this->user->{$this->storage->resource[$QueueArray[0]['i']]} . ' -> ' . ($this->user->{$this->storage->resource[$QueueArray[0]['i']]} + 1) . ')'];
			}
		}

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

		$parse['race'] = _getText('race', $this->user->race);

		$parse['xpminier'] = $this->user->xpminier;
		$parse['xpraid'] = $this->user->xpraid;
		$parse['lvl_minier'] = $this->user->lvl_minier;
		$parse['lvl_raid'] = $this->user->lvl_raid;
		$parse['user_id'] = $this->user->id;
		$parse['links'] = $this->user->links;

		$parse['raids_win'] = $this->user->raids_win;
		$parse['raids_lose'] = $this->user->raids_lose;
		$parse['raids'] = $this->user->raids;

		$parse['lvl_up_minier'] = $XpMinierUp;
		$parse['lvl_up_raid'] = $XpRaidUp;

		$parse['bonus'] = ($this->user->bonus < time()) ? true : false;

		if ($parse['bonus'])
		{
			$parse['bonus_multi'] = $this->user->bonus_multi + 1;

			if ($this->user->bonus < (time() - 86400))
				$parse['bonus_multi'] = 1;
		}

		$parse['refers'] = $this->user->refers;

		$parse['officiers'] = [];

		foreach ($this->storage->reslist['officier'] AS $officier)
		{
			$parse['officiers'][$officier] = $this->user->{$this->storage->resource[$officier]};
		}

		if (!$this->user->getUserOption('gameactivity'))
			$this->config->view->offsetSet('gameActivityList', 0);

		if ($this->config->view->get('gameActivityList', 0))
		{
			$parse['activity'] = ['chat' => [], 'forum' => []];

			$chat = json_decode($this->cache->get("chat"), true);

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

					$parse['activity']['chat'][] = [
						'TIME' => $message[1],
						'MESS' => '<span class="title"><span class="to">'.$message[2].'</span> написал'.(count($message[3]) ? ' <span class="to">'.implode(', ', $message[3]).'</span>' : '').'</span>: '.$message[5].''
					];

					$i++;
				}
			}

			$forum = $this->cache->get('forum_activity');

			if ($forum === null)
			{
				$forum = file_get_contents('http://forum.xnova.su/lastposts.php');

				$this->cache->save('forum_activity', $forum, 600);
			}

			$forum = json_decode($forum, true);

			foreach ($forum AS $message)
			{
				$parse['activity']['forum'][] = [
					'TIME' => $message['post_time'],
					'MESS' => '<span class="title"><span class="to">'.$message['username'].'</span> написал "<span class="to">'.$message['topic_title'].'</span>"</span>: '.Helpers::cutString(strip_tags($message['post_text']), 250).' <a href="http://forum.xnova.su/viewtopic.php?f='.$message['forum_id'].'&t='.$message['topic_id'].'&p='.$message['post_id'].'#p'.$message['post_id'].'" target="_blank">читать полностью</a>'
				];
			}

			//usort($parse['activity'], create_function('$a1,$a2', 'if ($a1["TIME"] == $a2["TIME"]) return 0; return ($a1["TIME"] < $a2["TIME"] ? 1 : -1);'));
		}

		$showMessage = false;

		foreach ($this->storage->reslist['res'] AS $res)
		{
			if (!$this->planet->{$res.'_mine_porcent'})
				$showMessage = true;
		}

		if ($showMessage)
			$this->view->setVar('globalMessage', '<span class="negative">Одна из шахт находится в выключенном состоянии. Зайдите в меню "Сырьё" и восстановите производство.</span>');

		$this->view->setVar('parse', $parse);
		$this->tag->setTitle('Обзор');
	}
}