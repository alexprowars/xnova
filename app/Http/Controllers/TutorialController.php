<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Format;
use Xnova\Controller;
use Xnova\Models;
use Xnova\Vars;

class TutorialController extends Controller
{
	private $loadPlanet = true;

	public function __construct ()
	{
		parent::__construct();

		$this->showTopPanel(false);
	}

	public function info (int $stage)
	{
		if ($stage <= 0)
			throw new PageException('Не выбрано задание', '/tutorial/');

		if (!__('tutorial.tutorial.'.$stage))
			throw new PageException('Задание не существует', '/tutorial/');

		$parse['info'] = __('tutorial.tutorial.'.$stage);
		$parse['task'] = [];
		$parse['rewd'] = [];

		/** @var Models\UsersQuest $qInfo */
		$qInfo = Models\UsersQuest::query()
			->where('user_id', $this->user->getId())
			->where('quest_id', $stage)
			->first();

		if (!$qInfo)
		{
			$qInfo = Models\UsersQuest::query()->create([
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
						$parse['task'][] = ['Исследовать <b>'.__('main.tech.'.$element).'</b> '.$level.' уровня', $check];
					elseif ($type == Vars::ITEM_TYPE_FLEET)
						$parse['task'][] = ['Построить '.$level.' ед. флота типа <b>'.__('main.tech.'.$element).'</b>', $check];
					elseif ($type == Vars::ITEM_TYPE_DEFENSE)
						$parse['task'][] = ['Построить '.$level.' ед. обороны типа <b>'.__('main.tech.'.$element).'</b>', $check];
					else
						$parse['task'][] = ['Построить <b>'.__('main.tech.'.$element).'</b> '.$level.' уровня', $check];
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
				$count = Models\Buddy::query()->where('sender', $this->user->id)->orWhere('owner', $this->user->id)->count();

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

				$parse['task'][] = ['Отправить флот в миссию: '.__('main.type_mission.'.$taskVal), $check];
			}

			if ($taskKey == 'PLANETS')
			{
				$count = Models\Planets::query()
					->where('id_owner', $this->user->getId())
					->where('planet_type', 1)
					->count();

				$check = $count >= $taskVal ? true : false;

				$parse['task'][] = ['Кол-во колонизированных планет: '.$taskVal, $check];
			}

			$errors += !$check ? 1 : 0;
		}

		if ($qInfo->finish > 0)
			$errors++;

		if (Input::has('continue') && !$errors && $qInfo->finish == 0)
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

			Cache::forget('app::quests::'.$this->user->getId());

			$this->user->save();
			$this->planet->save();

			throw new RedirectException('Квест завершен', '/tutorial/');
		}

		foreach ($parse['info']['REWARD'] AS $rewardKey => $rewardVal)
		{
			if ($rewardKey == 'metal')
				$parse['rewd'][] = Format::number($rewardVal).' ед. '.__('main.Metal').'а';
			elseif ($rewardKey == 'crystal')
				$parse['rewd'][] = Format::number($rewardVal).' ед. '.__('main.Crystal').'а';
			elseif ($rewardKey == 'deuterium')
				$parse['rewd'][] = Format::number($rewardVal).' ед. '.__('main.Deuterium').'';
			elseif ($rewardKey == 'credits')
				$parse['rewd'][] = Format::number($rewardVal).' ед. '.__('main.Credits').'';
			elseif ($rewardKey == 'BUILD')
			{
				foreach ($rewardVal AS $element => $level)
				{
					$type = Vars::getItemType($element);

					if ($type == Vars::ITEM_TYPE_TECH)
						$parse['rewd'][] = 'Исследование <b>'.__('main.tech.'.$element).'</b> '.$level.' уровня';
					elseif ($type == Vars::ITEM_TYPE_FLEET)
						$parse['rewd'][] = $level.' ед. флота типа <b>'.__('main.tech.'.$element).'</b>';
					elseif ($type == Vars::ITEM_TYPE_DEFENSE)
						$parse['rewd'][] = $level.' ед. обороны типа <b>'.__('main.tech.'.$element).'</b>';
					elseif ($type == Vars::ITEM_TYPE_OFFICIER)
						$parse['rewd'][] = 'Офицер <b>'.__('main.tech.'.$element).'</b> на '.round($level / 3600 / 24, 1).' суток';
					else
						$parse['rewd'][] = 'Постройка <b>'.__('main.tech.'.$element).'</b> '.$level.' уровня';
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

		$this->setTitle('Задание. '.$parse['info']['TITLE']);

		return $parse;
	}

	public function index ()
	{
		$parse = [];

		$userQuests = [];

		$quests = Models\UsersQuest::query()
			->where('user_id', $this->user->getId())
			->get();

		/** @var Models\UsersQuest $quest */
		foreach ($quests as $quest)
		{
			$userQuests[$quest->quest_id] = $quest->toArray();
		}

		$parse['list'] = [];
		$parse['quests'] = $userQuests;

		$quests = __('tutorial.tutorial');

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

		$this->setTitle('Обучение');

		return $parse;
	}
}