<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Models\Planet;
use Xnova\Models\User as UserModel;
use Phalcon\Di;

class Building
{
	/**
	 * @param  $user
	 * @param  $planet
	 * @param  $element
	 * @param bool $incremental
	 * @param bool $destroy
	 * @return bool
	 */
	static function isElementBuyable (UserModel $user, Planet $planet, $element, $incremental = true, $destroy = false)
	{
		$cost = self::getBuildingPrice($user, $planet, $element, $incremental, $destroy);

		foreach ($cost AS $ResType => $ResCount)
		{
			if ($ResType == 'energy')
			{
				if ($planet->energy_max < $ResCount)
					return false;
			}
			elseif (!isset($planet->{$ResType}) || $ResCount > $planet->{$ResType})
				return false;
		}

		return true;
	}

	static function isTechnologieAccessible (UserModel $user, Planet $planet, $element)
	{
		$requeriments = Vars::getItemRequirements($element);

		if (!count($requeriments))
			return true;

		$enabled = true;

		foreach ($requeriments as $reqElement => $level)
		{
			if ($reqElement == 700)
			{
				if ($user->race != $level)
					return false;
			}
			elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_TECH)
			{
				if ($user->getTechLevel($reqElement) < $level)
					return false;
			}
			elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_BUILING)
			{
				if ($planet->getBuildLevel($reqElement) < $level)
					return false;
			}
			elseif ($planet->planet_type == 5 && in_array($element, [43, 502, 503]) && !in_array($reqElement, [21, 41]))
				return false;
			else
				return false;
		}

		return $enabled;
	}

	static function checkTechnologyRace (UserModel $user, $element)
	{
		$requeriments = Vars::getItemRequirements($element);

		if (!count($requeriments))
			return true;

		foreach ($requeriments as $reqElement => $level)
		{
			if ($reqElement == 700 && $user->race != $level)
				return false;
		}

		return true;
	}

	static function checkLabSettingsInQueue (Planet $planet)
	{
		$queueManager = new Queue($planet->queue);

		if ($queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING))
		{
			$config = $planet->getDI()->getShared('config');

			$BuildQueue = $queueManager->get($queueManager::QUEUE_TYPE_BUILDING);

			if ($BuildQueue[0]['i'] == 31 && $config->game->get('BuildLabWhileRun', 0) != 1)
				return false;
			else
				return true;
		}

		return true;
	}

	static function getTechTree ($element, UserModel $user, Planet $planet)
	{
		$result = '';

		$requirements = Vars::getItemRequirements($element);

		if (!count($requirements))
			return $result;

		foreach ($requirements as $reqId => $level)
		{
			$minus = 0;

			if ($reqId != 700)
			{
				$elementType = Vars::getItemType($reqId);

				if ($elementType === Vars::ITEM_TYPE_TECH && $user->getTechLevel($reqId) >= $level)
					continue;
				elseif ($elementType == Vars::ITEM_TYPE_BUILING && $planet->getBuildLevel($reqId) >= $level)
					continue;

				if ($elementType == Vars::ITEM_TYPE_TECH && $user->getTechLevel($reqId) < $level)
					$minus = $level - $user->getTechLevel($reqId);
				elseif ($elementType == Vars::ITEM_TYPE_BUILING && $planet->getBuildLevel($reqId) < $level)
					$minus = $level - $planet->getBuildLevel($reqId);
			}
			else
			{
				if ($user->race == $level)
					continue;

				$level = _getText('race', $user->race);
				$minus = _getText('race', $level);
			}

			$result .= '<div><span class="negative">'._getText('tech', $reqId).' '.$level.($minus != 0 ? ' ('.$minus.')' : '').'</span></div>';
		}

		return $result;
	}

	/**
	 * @param  $user UserModel
	 * @param  $planet Planet
	 * @param  $element integer
	 * @return int
	 */
	static function getBuildingTime (UserModel $user, Planet $planet, $element)
	{
		$config = $user->getDI()->getShared('config');

		$elementType = Vars::getItemType($element);
		$time = 0;

		$cost = self::getBuildingPrice($user, $planet, $element, !in_array($elementType,  [Vars::ITEM_TYPE_DEFENSE, Vars::ITEM_TYPE_FLEET]), false, false);
		$cost = $cost['metal'] + $cost['crystal'];

		if ($elementType == Vars::ITEM_TYPE_BUILING)
		{
			$time = ($cost / $config->game->get('game_speed')) * (1 / ($planet->getBuildLevel('nano_factory') + 1)) * pow(0.5, $planet->getBuildLevel('robot_factory'));
			$time = floor($time * 3600 * $user->bonusValue('time_building'));
		}
		elseif ($elementType == Vars::ITEM_TYPE_TECH)
		{
			if (isset($planet->spaceLabs) && count($planet->spaceLabs))
			{
				$lablevel = 0;

				foreach ($planet->spaceLabs as $Levels)
				{
					$req = Vars::getItemRequirements($element);

					if (!isset($req[31]) || $Levels >= $req[31])
						$lablevel += $Levels;
				}
			}
			else
				$lablevel = $planet->getBuildLevel('laboratory');

			$time = ($cost / $config->game->get('game_speed')) / (($lablevel + 1) * 2);
			$time = floor($time * 3600 * $user->bonusValue('time_research'));
		}
		elseif ($elementType == Vars::ITEM_TYPE_DEFENSE)
		{
			$time = ($cost / $config->game->get('game_speed')) * (1 / ($planet->getBuildLevel('hangar') + 1)) * pow(1 / 2, $planet->getBuildLevel('nano_factory'));
			$time = floor($time * 3600 * $user->bonusValue('time_defence'));
		}
		elseif ($elementType == Vars::ITEM_TYPE_FLEET)
		{
			$time = ($cost / $config->game->get('game_speed')) * (1 / ($planet->getBuildLevel('hangar') + 1)) * pow(1 / 2, $planet->getBuildLevel('nano_factory'));
			$time = floor($time * 3600 * $user->bonusValue('time_fleet'));
		}

		$time = max(1, $time);

		return $time;
	}

	/**
	 * @param $cost array
	 * @param  $planet Planet
	 * @return string
	 */
	static function getElementPrice ($cost, Planet $planet)
	{
		$array = [
			'metal' 	=> _getText('Metal'),
			'crystal' 	=> _getText('Crystal'),
			'deuterium' => _getText('Deuterium'),
			'energy'	=> _getText('Energy')
		];

		$uri = Di::getDefault()->getShared('url');

		$text = "";

		foreach ($array as $type => $title)
		{
			if (isset($cost[$type]) && $cost[$type] != 0)
			{
				$text .= "<div><img src='".$uri->get('assets/images/skin/s_'.$type.'.png')."' align=\"absmiddle\" class=\"tooltip\" data-content='".$title."'>";

				if ($cost[$type] > $planet->{$type})
					$text .= "<span class=\"resNo tooltip\" data-content=\"необходимо: ".Format::number($cost[$type] - $planet->{$type})."\">" . Format::number($cost[$type]) . "</span> ";
				else
					$text .= "<span class=\"resYes\">" . Format::number($cost[$type]) . "</span> ";

				$text .= "</div>";
			}
		}

		return $text;
	}

	/**
	 * @param $user UserModel
	 * @param $planet Planet
	 * @param $element
	 * @param bool $incremental
	 * @param bool $destroy
	 * @param bool $withBonus
	 * @return array
	 */
	static function getBuildingPrice (UserModel $user, Planet $planet, $element, $incremental = true, $destroy = false, $withBonus = true)
	{
		$price = Vars::getItemPrice($element);
		$elementType = Vars::getItemType($element);
		$level = 0;

		if ($incremental)
		{
			if ($elementType == Vars::ITEM_TYPE_BUILING)
				$level = $planet->getBuildLevel($element);
			else
				$level = $user->getTechLevel($element);
		}

		$cost = [];

		foreach (['metal', 'crystal', 'deuterium', 'energy'] as $ResType)
		{
			if (!isset($price[$ResType]))
				continue;

			if ($incremental && isset($price['factor']))
				$cost[$ResType] = floor($price[$ResType] * pow($price['factor'], $level));
			else
				$cost[$ResType] = floor($price[$ResType]);

			if ($withBonus)
			{
				switch ($elementType)
				{
					case Vars::ITEM_TYPE_BUILING:
						$cost[$ResType] *= $user->bonusValue('res_building');
						break;
					case Vars::ITEM_TYPE_TECH:
						$cost[$ResType] *= $user->bonusValue('res_research');
						break;
					case Vars::ITEM_TYPE_FLEET:
						$cost[$ResType] *= $user->bonusValue('res_fleet');
						break;
					case Vars::ITEM_TYPE_DEFENSE:
						$cost[$ResType] *= $user->bonusValue('res_defence');
						break;
				}

				$cost[$ResType] = round($cost[$ResType]);
			}

			if ($destroy)
				$cost[$ResType] = floor($cost[$ResType] / 2);
		}

		return $cost;
	}

	/**
	 * @param int $Element
	 * @param int $Level
	 * @param Planet $planet
	 * @return string
	 */
	static function getNextProduction ($Element, $Level, Planet $planet)
	{
		if (!in_array($Element, Vars::getItemsByType('prod')))
			return '';

		$Res = [];

		$resFrom = $planet->getResourceProductionLevel($Element, ($Level + 1));

		$Res['m'] = $resFrom['metal'];
		$Res['c'] = $resFrom['crystal'];
		$Res['d'] = $resFrom['deuterium'];
		$Res['e'] = $resFrom['energy'];

		$resTo = $planet->getResourceProductionLevel($Element, $Level);

		$Res['m'] -= $resTo['metal'];
		$Res['c'] -= $resTo['crystal'];
		$Res['d'] -= $resTo['deuterium'];
		$Res['e'] -= $resTo['energy'];

		$text = '';

		if ($Res['m'] != 0)
			$text .= '<div class="building-effects-row"><span class="sprite skin_s_metal" title="Металл"></span> <span class=' . (($Res['m'] > 0) ? 'positive' : 'negative') . ">" . abs($Res['m']) . '</span></div>';

		if ($Res['c'] != 0)
			$text .= '<div class="building-effects-row"><span class="sprite skin_s_crystal" title="Кристалл"></span> <span class=' . (($Res['c'] > 0) ? 'positive' : 'negative') . ">" . abs($Res['c']) . '</span></div>';

		if ($Res['d'] != 0)
			$text .= '<div class="building-effects-row"><span class="sprite skin_s_deuterium" title="Дейтерий"></span> <span class=' . (($Res['d'] > 0) ? 'positive' : 'negative') . ">" . abs($Res['d']) . '</span></div>';

		if ($Res['e'] != 0)
			$text .= '<div class="building-effects-row"><span class="sprite skin_s_energy" title="Энергия"></span> <span class=' . (($Res['e'] > 0) ? 'positive' : 'negative') . ">" . abs($Res['e']) . '</span></div>';

		return $text;
	}

	/**
	 * @param $element int
	 * @param $count int
	 * @param $user UserModel
	 * @return mixed
	 */
	static function getElementRessources ($element, $count, UserModel $user)
	{
		$price = Vars::getItemPrice($element);

		$ResType['metal'] 		= $price['metal'] * $count;
		$ResType['crystal'] 	= $price['crystal'] * $count;
		$ResType['deuterium'] 	= $price['deuterium'] * $count;

		foreach ($ResType AS &$cost)
		{
			switch (Vars::getItemType($element))
			{
				case Vars::ITEM_TYPE_FLEET:
					$cost = round($cost * $user->bonusValue('res_fleet'));
					break;
				case Vars::ITEM_TYPE_DEFENSE:
					$cost = round($cost * $user->bonusValue('res_defence'));
					break;
			}
		}

		unset($cost);

		return $ResType;
	}

	/**
	 * @param $element int
	 * @param $planet Planet
	 * @param $user UserModel
	 * @return float|int
	 */
	static function getMaxConstructibleElements ($element, Planet $planet, UserModel $user)
	{
		$max = -1;

		$price = self::getElementRessources($element, 1, $user);

		foreach ($price as $resType => $resCount)
		{
			if (in_array($resType, ['metal', 'crystal', 'deuterium', 'energy']) && $resCount != 0)
			{
				$count = floor($planet->{$resType} / $resCount);

				if ($max < 0)
					$max = $count;
				elseif ($max > $count)
					$max = $count;
			}
		}

		if (isset($price['max']) && $max > $price['max'])
			$max = $price['max'];

		return $max;
	}
}