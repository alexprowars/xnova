<?php

namespace App\Controllers;

use App\Helpers;
use App\Lang;
use App\Sql;

class TutorialController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		$this->user->loadPlanet();
	}
	
	public function indexAction ()
	{
		global $resource, $reslist;

		$parse = array();
		
		$requer = 0;

		Lang::includeLang('tutorial');

		$stage = request::G('q', 0, VALUE_INT);

		if ($stage > 0)
		{
			$parse['info'] = _getText('tutorial', $stage);
			$parse['task'] = array();
			$parse['rewd'] = array();

			$qInfo = $this->db->query("SELECT * FROM game_users_quests WHERE user_id = ".$this->user->getId()." AND quest_id = ".$stage."")->fetch();

			if (!isset($qInfo['id']))
			{
				$qInfo = array
				(
					'user_id' => $this->user->getId(),
					'quest_id' => $stage,
					'finish' => 0,
					'stage' => 0
				);

				$qInfo['id'] = Sql::build()->insert('game_users_quests')->set($qInfo)->execute();
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
						$check = isset($this->user->data[$resource[$element]]) ? ($this->user->data[$resource[$element]] >= $level) : ($this->planet->data[$resource[$element]] >= $level);

						if ($chk == true)
							$chk = $check;

						if (in_array($element, array_merge($reslist['tech'], $reslist['tech_f'])))
							$parse['task'][] = array('Исследовать <b>'._getText('tech', $element).'</b> '.$level.' уровня', $check);
						elseif (in_array($element, $reslist['fleet']))
							$parse['task'][] = array('Постороить '.$level.' ед. флота типа <b>'._getText('tech', $element).'</b>', $check);
						elseif (in_array($element, $reslist['defense']))
							$parse['task'][] = array('Постороить '.$level.' ед. обороны типа <b>'._getText('tech', $element).'</b>', $check);
						else
							$parse['task'][] = array('Построить <b>'._getText('tech', $element).'</b> '.$level.' уровня', $check);
					}

					$check = $chk;
				}

				if ($taskKey == '!PLANET_NAME')
				{
					$check = $this->planet->name != $taskVal ? true : false;

					$parse['task'][] = array('Переименовать планету', $check);
				}

				if ($taskKey == 'BUDDY_COUNT')
				{
					$count = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_buddy WHERE sender = ".$this->user->data['id']." OR owner = ".$this->user->data['id']."");

					$check = $count >= $taskVal ? true : false;

					$parse['task'][] = array('Кол-во друзей в игре: '.$taskVal, $check);
				}

				if ($taskKey == 'ALLY')
				{
					$check = $this->user->data['ally_id'] > 0 ? true : false;

					$parse['task'][] = array('Вступить в альянс с кол-во игроков: '.$taskVal, $check);
				}

				if ($taskKey == 'STORAGE')
				{
					if ($taskVal === true)
					{
						$check = $this->planet->data[$resource[22]] > 0 || $this->planet->data[$resource[23]] > 0 || $this->planet->data[$resource[24]] > 0;

						$parse['task'][] = array('Построить любое хранилище ресурсов', $check);
					}
				}

				if ($taskKey == 'TRADE')
				{
					$check = $qInfo['stage'] > 0 ? true : false;

					$parse['task'][] = array('Обменять ресурсы у торговца', $check);
				}

				if ($taskKey == 'FLEET_MISSION')
				{
					$check = $qInfo['stage'] > 0 ? true : false;

					$parse['task'][] = array('Отправить флот в миссию: '._getText('type_mission', $taskVal), $check);
				}

				if ($taskKey == 'PLANETS')
				{
					$count = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_planets WHERE id_owner = ".$this->user->getId()." AND planet_type = 1");

					$check = $count >= $taskVal ? true : false;

					$parse['task'][] = array('Кол-во колонизированных планет: '.$taskVal, $check);
				}

				$errors += !$check ? 1 : 0;
			}

			if ($qInfo['finish'] > 0)
				$errors++;

			if (isset($_GET['continue']) && !$errors && $qInfo['finish'] == 0)
			{
				//$this->db->query("UPDATE game_planets SET `" . $resource[401] . "` = `" . $resource[401] . "` + 3 WHERE `id` = '" . $this->planet->id . "';");

				$planetData = array();
				$userData = array();

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
							if (in_array($element, array_merge($reslist['tech'], $reslist['tech_f'])))
								$userData['+'.$resource[$element]] = $level;
							elseif (in_array($element, $reslist['fleet']))
								$planetData['+'.$resource[$element]] = $level;
							elseif (in_array($element, $reslist['defense']))
								$planetData['+'.$resource[$element]] = $level;
							elseif (in_array($element, $reslist['officier']))
							{
								if ($this->user->data[$resource[$element]] > time())
									$userData['+'.$resource[$element]] = $level;
								else
									$userData[$resource[$element]] = time() + $level;
							}
							else
								$planetData['+'.$resource[$element]] = $level;
						}
					}
					elseif ($rewardKey == 'STORAGE_RAND')
					{
						$r = mt_rand(22, 24);

						$planetData['+'.$resource[$r]] = 1;
					}
				}

				Sql::build()->update('game_users_quests')->setField('finish', '1')->where('id', '=', $qInfo['id'])->execute();

				if (count($planetData))
					$this->planet->saveData($planetData);
				if (count($userData))
					$this->user->saveData($userData);

				$this->response->redirect('?set=tutorial');
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
						if (in_array($element, array_merge($reslist['tech'], $reslist['tech_f'])))
							$parse['rewd'][] = 'Исследование <b>'._getText('tech', $element).'</b> '.$level.' уровня';
						elseif (in_array($element, $reslist['fleet']))
							$parse['rewd'][] = $level.' ед. флота типа <b>'._getText('tech', $element).'</b>';
						elseif (in_array($element, $reslist['defense']))
							$parse['rewd'][] = $level.' ед. обороны типа <b>'._getText('tech', $element).'</b>';
						elseif (in_array($element, $reslist['officier']))
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

			$this->view->pick('quests_info');

			$this->view->setVar('stage', $stage);
			$this->view->setVar('errors', $errors);

			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Задание. '.$parse['info']['TITLE']);
			$this->showTopPanel(false);
		}

		$userQuests = array();

		$dbRes = $this->db->query("SELECT * FROM game_users_quests WHERE user_id = ".$this->user->getId()."");

		while ($res = $dbRes->fetch())
		{
			$userQuests[$res['quest_id']] = $res;
		}

		$parse['list'] = array();
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

		$this->view->pick('quests_list');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Обучение');
		$this->showTopPanel(false);
	}
}

?>