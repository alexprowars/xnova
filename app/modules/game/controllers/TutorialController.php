<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Helpers;
use App\Lang;
use App\Models\Planet;

class TutorialController extends Application
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('tutorial');

		$this->user->loadPlanet();

		$this->showTopPanel(false);
	}

	public function infoAction ()
	{
		$stage = $this->request->getQuery('id', 'int', 0);

		if ($stage > 0)
		{
			$parse['info'] = _getText('tutorial', $stage);
			$parse['task'] = [];
			$parse['rewd'] = [];

			$qInfo = $this->db->query("SELECT * FROM game_users_quests WHERE user_id = ".$this->user->getId()." AND quest_id = ".$stage."")->fetch();

			if (!isset($qInfo['id']))
			{
				$qInfo = [
					'user_id' 	=> $this->user->getId(),
					'quest_id' 	=> $stage,
					'finish' 	=> 0,
					'stage' 	=> 0
				];

				$this->db->insertAsDict('game_users_quests', $qInfo);

				$qInfo['id'] = $this->db->lastInsertId();
			}

			$errors = 0;

			foreach ($parse['info']['TASK'] AS $taskKey => $taskVal)
			{
				$check = false;

				if ($taskKey == 'BUILD')
				{
					$chk = true;

					foreach ($taskVal AS $element => $level)
					{
						$check = isset($this->user->{$this->storage->resource[$element]}) ? ($this->user->{$this->storage->resource[$element]} >= $level) : ($this->planet->{$this->storage->resource[$element]} >= $level);

						if ($chk == true)
							$chk = $check;

						if (in_array($element, array_merge($this->storage->reslist['tech'], $this->storage->reslist['tech_f'])))
							$parse['task'][] = ['Исследовать <b>'._getText('tech', $element).'</b> '.$level.' уровня', $check];
						elseif (in_array($element, $this->storage->reslist['fleet']))
							$parse['task'][] = ['Постороить '.$level.' ед. флота типа <b>'._getText('tech', $element).'</b>', $check];
						elseif (in_array($element, $this->storage->reslist['defense']))
							$parse['task'][] = ['Постороить '.$level.' ед. обороны типа <b>'._getText('tech', $element).'</b>', $check];
						else
							$parse['task'][] = ['Построить <b>'._getText('tech', $element).'</b> '.$level.' уровня', $check];
					}

					$check = $chk;
				}

				if ($taskKey == '!PLANET_NAME')
				{
					$check = $this->planet->name != $taskVal ? true : false;

					$parse['task'][] = ['Переименовать планету', $check];
				}

				if ($taskKey == 'BUDDY_COUNT')
				{
					$count = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_buddy WHERE sender = ".$this->user->id." OR owner = ".$this->user->id."");

					$check = $count >= $taskVal ? true : false;

					$parse['task'][] = ['Кол-во друзей в игре: '.$taskVal, $check];
				}

				if ($taskKey == 'ALLY')
				{
					$check = $this->user->ally_id > 0 ? true : false;

					$parse['task'][] = ['Вступить в альянс с кол-во игроков: '.$taskVal, $check];
				}

				if ($taskKey == 'STORAGE')
				{
					if ($taskVal === true)
					{
						$check = $this->planet->{$this->storage->resource[22]} > 0 || $this->planet->{$this->storage->resource[23]} > 0 || $this->planet->{$this->storage->resource[24]} > 0;

						$parse['task'][] = ['Построить любое хранилище ресурсов', $check];
					}
				}

				if ($taskKey == 'TRADE')
				{
					$check = $qInfo['stage'] > 0 ? true : false;

					$parse['task'][] = ['Обменять ресурсы у торговца', $check];
				}

				if ($taskKey == 'FLEET_MISSION')
				{
					$check = $qInfo['stage'] > 0 ? true : false;

					$parse['task'][] = ['Отправить флот в миссию: '._getText('type_mission', $taskVal), $check];
				}

				if ($taskKey == 'PLANETS')
				{
					$count = Planet::count(['id_owner = ?0 AND planet_type = 1', 'bind' => [$this->user->getId()]]);

					$check = $count >= $taskVal ? true : false;

					$parse['task'][] = ['Кол-во колонизированных планет: '.$taskVal, $check];
				}

				$errors += !$check ? 1 : 0;
			}

			if ($qInfo['finish'] > 0)
				$errors++;

			if ($this->request->hasQuery('continue') && !$errors && $qInfo['finish'] == 0)
			{
				//$this->db->query("UPDATE game_planets SET `" . $this->storage->resource[401] . "` = `" . $this->storage->resource[401] . "` + 3 WHERE `id` = '" . $this->planet->id . "';");

				$planetData = [];
				$userData = [];

				foreach ($parse['info']['REWARD'] AS $rewardKey => $rewardVal)
				{
					if ($rewardKey == 'metal')
						$planetData['+metal'] = $rewardVal;
					elseif ($rewardKey == 'crystal')
						$planetData['+crystal'] = $rewardVal;
					elseif ($rewardKey == 'deuterium')
						$planetData['+deuterium'] = $rewardVal;
					elseif ($rewardKey == 'credits')
						$userData['+credits'] = $rewardVal;
					elseif ($rewardKey == 'BUILD')
					{
						foreach ($rewardVal AS $element => $level)
						{
							if (in_array($element, array_merge($this->storage->reslist['tech'], $this->storage->reslist['tech_f'])))
								$userData['+'.$this->storage->resource[$element]] = $level;
							elseif (in_array($element, $this->storage->reslist['fleet']))
								$planetData['+'.$this->storage->resource[$element]] = $level;
							elseif (in_array($element, $this->storage->reslist['defense']))
								$planetData['+'.$this->storage->resource[$element]] = $level;
							elseif (in_array($element, $this->storage->reslist['officier']))
							{
								if ($this->user->{$this->storage->resource[$element]} > time())
									$userData['+'.$this->storage->resource[$element]] = $level;
								else
									$userData[$this->storage->resource[$element]] = time() + $level;
							}
							else
								$planetData['+'.$this->storage->resource[$element]] = $level;
						}
					}
					elseif ($rewardKey == 'STORAGE_RAND')
					{
						$r = mt_rand(22, 24);

						$planetData['+'.$this->storage->resource[$r]] = 1;
					}
				}

				$this->db->updateAsDict('game_users_quests', ['finish' => '1'], 'id = '.$qInfo['id']);

				if (count($planetData))
					$this->planet->saveData($planetData);
				if (count($userData))
					$this->user->saveData($userData);

				$this->response->redirect('tutorial/');
			}

			foreach ($parse['info']['REWARD'] AS $rewardKey => $rewardVal)
			{
				if ($rewardKey == 'metal')
					$parse['rewd'][] = Helpers::pretty_number($rewardVal).' ед. '._getText('Metal').'а';
				elseif ($rewardKey == 'crystal')
					$parse['rewd'][] = Helpers::pretty_number($rewardVal).' ед. '._getText('Crystal').'а';
				elseif ($rewardKey == 'deuterium')
					$parse['rewd'][] = Helpers::pretty_number($rewardVal).' ед. '._getText('Deuterium').'';
				elseif ($rewardKey == 'credits')
					$parse['rewd'][] = Helpers::pretty_number($rewardVal).' ед. '._getText('Credits').'';
				elseif ($rewardKey == 'BUILD')
				{
					foreach ($rewardVal AS $element => $level)
					{
						if (in_array($element, array_merge($this->storage->reslist['tech'], $this->storage->reslist['tech_f'])))
							$parse['rewd'][] = 'Исследование <b>'._getText('tech', $element).'</b> '.$level.' уровня';
						elseif (in_array($element, $this->storage->reslist['fleet']))
							$parse['rewd'][] = $level.' ед. флота типа <b>'._getText('tech', $element).'</b>';
						elseif (in_array($element, $this->storage->reslist['defense']))
							$parse['rewd'][] = $level.' ед. обороны типа <b>'._getText('tech', $element).'</b>';
						elseif (in_array($element, $this->storage->reslist['officier']))
							$parse['rewd'][] = 'Офицер <b>'._getText('tech', $element).'</b> на '.round($level / 3600 / 24, 1).' суток';
						else
							$parse['rewd'][] = 'Постройка <b>'._getText('tech', $element).'</b> '.$level.' уровня';
					}
				}
				elseif ($rewardKey == 'STORAGE_RAND')
				{
					$parse['rewd'][] = '+1 уровень одного из хранилищ ресурсов';
				}
			}

			$this->view->setVar('stage', $stage);
			$this->view->setVar('errors', $errors);

			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Задание. '.$parse['info']['TITLE']);
		}
	}
	
	public function indexAction ()
	{
		$parse = [];

		$userQuests = [];

		$dbRes = $this->db->query("SELECT * FROM game_users_quests WHERE user_id = ".$this->user->getId()."");

		while ($res = $dbRes->fetch())
		{
			$userQuests[$res['quest_id']] = $res;
		}

		$parse['list'] = [];
		$parse['quests'] = $userQuests;

		$quests = _getText('tutorial');

		foreach ($quests AS $qId => $quest)
		{
			$available = true;

			if (isset($quest['REQUIRED']))
			{
				foreach ($quest['REQUIRED'] AS $key => $req)
				{
					if ($key == 'QUEST' && (!isset($userQuests[$req]) || (isset($userQuests[$req]) && $userQuests[$req]['finish'] == 0)))
						$available = false;

					if ($key == 'LEVEL_MINIER' && $this->user->lvl_minier < $req)
						$available = false;

					if ($key == 'LEVEL_RAID' && $this->user->lvl_raid < $req)
						$available = false;
				}
			}

			$quest['ID'] = $qId;
			$quest['FINISH'] = (isset($userQuests[$qId]) && $userQuests[$qId]['finish'] == 1);
			$quest['AVAILABLE'] = $available;

			$parse['list'][] = $quest;
		}

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Обучение');
	}
}