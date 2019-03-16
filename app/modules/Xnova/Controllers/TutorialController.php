<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Format;
use Friday\Core\Lang;
use Xnova\Models\Buddy;
use Xnova\Models\Planet;
use Xnova\Controller;
use Xnova\Models\UserQuest;
use Xnova\Request;
use Xnova\Vars;

/**
 * @RoutePrefix("/tutorial")
 * @Route("/")
 * @Private
 */
class TutorialController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('tutorial', 'xnova');

		$this->user->loadPlanet();

		$this->showTopPanel(false);
	}

	/**
	 * @Route("/{stage:[0-9]+}{params:(/.*)*}")
	 * @param $stage
	 * @return void
	 * @throws PageException
	 * @throws RedirectException
	 */
	public function infoAction ($stage)
	{
		$stage = (int) $stage;

		if ($stage <= 0)
			throw new PageException('Не выбрано задание', '/tutorial/');

		if (!_getText('tutorial', $stage))
			throw new PageException('Задание не существует', '/tutorial/');

		$parse['info'] = _getText('tutorial', $stage);
		$parse['task'] = [];
		$parse['rewd'] = [];

		/** @var UserQuest $qInfo */
		$qInfo = UserQuest::query()
			->where('user_id = :user: AND quest_id = :quest:')
			->bind(['user' => $this->user->getId(), 'quest' => $stage])
			->execute()->getFirst();

		if (!$qInfo)
		{
			$qInfo = new UserQuest();

			$qInfo->create([
				'user_id' 	=> $this->user->getId(),
				'quest_id' 	=> $stage,
				'finish' 	=> 0,
				'stage' 	=> 0
			]);
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
					$type = Vars::getItemType($element);

					if ($type == Vars::ITEM_TYPE_TECH)
						$check = $this->user->getTechLevel($element) >= $level;
					elseif ($type == Vars::ITEM_TYPE_FLEET || $type == Vars::ITEM_TYPE_DEFENSE)
						$check = $this->planet->getUnitCount($element) >= $level;
					else
						$check = $this->planet->getBuildLevel($element) >= $level;

					if ($chk == true)
						$chk = $check;

					if ($type == Vars::ITEM_TYPE_TECH)
						$parse['task'][] = ['Исследовать <b>'._getText('tech', $element).'</b> '.$level.' уровня', $check];
					elseif ($type == Vars::ITEM_TYPE_FLEET)
						$parse['task'][] = ['Построить '.$level.' ед. флота типа <b>'._getText('tech', $element).'</b>', $check];
					elseif ($type == Vars::ITEM_TYPE_DEFENSE)
						$parse['task'][] = ['Построить '.$level.' ед. обороны типа <b>'._getText('tech', $element).'</b>', $check];
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
				$count = Buddy::count(['sender = ?0 OR owner = ?0',
					'bind' => [$this->user->id]]
				);

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
					$check = $this->planet->getBuildLevel('metal_store') > 0 || $this->planet->getBuildLevel('crystal_store') > 0 || $this->planet->getBuildLevel('deuterium_store') > 0;

					$parse['task'][] = ['Построить любое хранилище ресурсов', $check];
				}
			}

			if ($taskKey == 'TRADE')
			{
				$check = $qInfo->stage > 0;

				$parse['task'][] = ['Обменять ресурсы у торговца', $check];
			}

			if ($taskKey == 'FLEET_MISSION')
			{
				$check = $qInfo->stage > 0;

				$parse['task'][] = ['Отправить флот в миссию: '._getText('type_mission', $taskVal), $check];
			}

			if ($taskKey == 'PLANETS')
			{
				$count = Planet::count(['id_owner = ?0 AND planet_type = 1',
					'bind' => [$this->user->getId()]]
				);

				$check = $count >= $taskVal ? true : false;

				$parse['task'][] = ['Кол-во колонизированных планет: '.$taskVal, $check];
			}

			$errors += !$check ? 1 : 0;
		}

		if ($qInfo->finish > 0)
			$errors++;

		if ($this->request->hasQuery('continue') && !$errors && $qInfo->finish == 0)
		{
			foreach ($parse['info']['REWARD'] AS $rewardKey => $rewardVal)
			{
				if ($rewardKey == 'metal')
					$this->planet->metal += $rewardVal;
				elseif ($rewardKey == 'crystal')
					$this->planet->crystal += $rewardVal;
				elseif ($rewardKey == 'deuterium')
					$this->planet->deuterium += $rewardVal;
				elseif ($rewardKey == 'credits')
					$this->user->credits += $rewardVal;
				elseif ($rewardKey == 'BUILD')
				{
					foreach ($rewardVal AS $element => $level)
					{
						$type = Vars::getItemType($element);

						if ($type == Vars::ITEM_TYPE_TECH)
							$this->user->setTech($element, $this->user->getTechLevel($element) + (int) $level);
						elseif ($type == Vars::ITEM_TYPE_FLEET || $type == Vars::ITEM_TYPE_DEFENSE)
							$this->planet->setUnit($element, $level, true);
						elseif ($type == Vars::ITEM_TYPE_OFFICIER)
						{
							if ($this->user->{Vars::getName($element)} > time())
								$this->user->{Vars::getName($element)} += $level;
							else
								$this->user->{Vars::getName($element)} = time() + $level;
						}
						elseif ($type == Vars::ITEM_TYPE_BUILING)
							$this->planet->setBuild($element, $this->planet->getBuildLevel($element) + (int) $level);
					}
				}
				elseif ($rewardKey == 'STORAGE_RAND')
				{
					$r = mt_rand(22, 24);

					$this->planet->setBuild($r, $this->planet->getBuildLevel($r) + 1);
				}
			}

			$qInfo->finish = 1;
			$qInfo->update();

			$this->cache->delete('app::quests::'.$this->user->getId());

			$this->user->save();
			$this->planet->save();

			throw new RedirectException('Квест завершен', '/tutorial/');
		}

		foreach ($parse['info']['REWARD'] AS $rewardKey => $rewardVal)
		{
			if ($rewardKey == 'metal')
				$parse['rewd'][] = Format::number($rewardVal).' ед. '._getText('Metal').'а';
			elseif ($rewardKey == 'crystal')
				$parse['rewd'][] = Format::number($rewardVal).' ед. '._getText('Crystal').'а';
			elseif ($rewardKey == 'deuterium')
				$parse['rewd'][] = Format::number($rewardVal).' ед. '._getText('Deuterium').'';
			elseif ($rewardKey == 'credits')
				$parse['rewd'][] = Format::number($rewardVal).' ед. '._getText('Credits').'';
			elseif ($rewardKey == 'BUILD')
			{
				foreach ($rewardVal AS $element => $level)
				{
					$type = Vars::getItemType($element);

					if ($type == Vars::ITEM_TYPE_TECH)
						$parse['rewd'][] = 'Исследование <b>'._getText('tech', $element).'</b> '.$level.' уровня';
					elseif ($type == Vars::ITEM_TYPE_FLEET)
						$parse['rewd'][] = $level.' ед. флота типа <b>'._getText('tech', $element).'</b>';
					elseif ($type == Vars::ITEM_TYPE_DEFENSE)
						$parse['rewd'][] = $level.' ед. обороны типа <b>'._getText('tech', $element).'</b>';
					elseif ($type == Vars::ITEM_TYPE_OFFICIER)
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

		$parse['rewd'] = implode(', ', $parse['rewd']);
		$parse['stage'] = $stage;
		$parse['errors'] = $errors;

		Request::addData('page', $parse);

		$this->tag->setTitle('Задание. '.$parse['info']['TITLE']);
	}
	
	public function indexAction ()
	{
		$parse = [];

		$userQuests = [];

		$quests = UserQuest::query()
			->where('user_id = :user:')
			->bind(['user' => $this->user->getId()])
			->execute();

		/** @var UserQuest $quest */
		foreach ($quests as $quest)
		{
			$userQuests[$quest->quest_id] = $quest->toArray();
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

		Request::addData('page', $parse);

		$this->tag->setTitle('Обучение');
	}
}