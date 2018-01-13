<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Models\Planet;
use Xnova\Models\User;
use Phalcon\Di;

class Building
{
	/**
	 * @param  $user
	 * @param  $planet
	 * @param  $Element
	 * @param bool $incremental
	 * @param bool $destroy
	 * @return bool
	 */
	static function IsElementBuyable (User $user, Planet $planet, $Element, $incremental = true, $destroy = false)
	{
		$cost = self::GetBuildingPrice($user, $planet, $Element, $incremental, $destroy);

		foreach ($cost AS $ResType => $ResCount)
		{
			if (!isset($planet->{$ResType}) || $ResCount > $planet->{$ResType})
				return false;
		}

		return true;
	}

	static function IsTechnologieAccessible (User $user, Planet $planet, $element)
	{
		$requeriments = Vars::getItemRequeriments($element);

		if (!count($requeriments))
			return true;

		$enabled = true;

		foreach ($requeriments as $reqElement => $level)
		{
			if ($reqElement == 700 && $user->race != $level)
				return false;
			elseif (Vars::getItemType($reqElement) == Vars::ITEM_TYPE_TECH && $user->getTechLevel($reqElement) >= $level)
			{
				// break;
			}
			elseif (Vars::getItemType($reqElement) != Vars::ITEM_TYPE_TECH && $planet->getBuildLevel($reqElement) >= $level)
				$enabled = true;
			elseif (isset($planet->planet_type) && $planet->planet_type == 5 && ($element == 43 || $element == 502 || $element == 503) && ($reqElement == 21 || $reqElement == 41))
				$enabled = true;
			else
				return false;
		}

		return $enabled;
	}

	static function checkTechnologyRace (User $user, $element)
	{
		$requeriments = Vars::getItemRequeriments($element);

		if (!count($requeriments))
			return true;

		foreach ($requeriments as $reqElement => $level)
		{
			if ($reqElement == 700 && $user->race != $level)
				return false;
		}

		return true;
	}

	static function CheckLabSettingsInQueue (Planet $planet)
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

	static function getTechTree ($element, User $user, Planet $planet)
	{
		$result = '';

		$requeriments = Vars::getItemRequeriments($element);
		$elementType = Vars::getItemType($element);

		if (!count($requeriments))
			return $result;

		foreach ($requeriments as $ResClass => $Level)
		{
			$isAvailable = false;
			$line = '';

			if ($ResClass != 700)
			{
				if (in_array($elementType, [Vars::ITEM_TYPE_TECH, Vars::ITEM_TYPE_TECH_FLEET]) && $user->getTechLevel($ResClass) >= $Level)
					$isAvailable = true;
				elseif ($elementType == Vars::ITEM_TYPE_BUILING && $planet->getBuildLevel($ResClass) >= $Level)
					$isAvailable = true;

				$line .= _getText('level').' '.$Level;

				if (in_array($elementType, [Vars::ITEM_TYPE_TECH, Vars::ITEM_TYPE_TECH_FLEET]) && $user->getTechLevel($ResClass) < $Level)
				{
					$minus = $Level - $user->getTechLevel($ResClass);
					$line .= " + <b>" . $minus . "</b>";
				}
				elseif ($elementType == Vars::ITEM_TYPE_BUILING && $planet->getBuildLevel($ResClass) < $Level)
				{
					$minus = $Level - $planet->getBuildLevel($ResClass);
					$line .= " + <b>" . $minus . "</b>";
				}
			}
			else
			{
				$line .= _getText('race', $Level);
				$isAvailable = ($user->race == $Level);
			}

			$result .= '<span class="'.($isAvailable ? 'positive' : 'negative').'">'._getText('tech', $ResClass).' ('.$line.')</span><br>';
		}

		return $result;
	}

	/**
	 * @param  $user User
	 * @param  $planet Planet
	 * @param  $element integer
	 * @return int
	 */
	static function GetBuildingTime (User $user, Planet $planet, $element)
	{
		$config = $user->getDI()->getShared('config');

		$elementType = Vars::getItemType($element);
		$time = 0;

		$cost = self::GetBuildingPrice($user, $planet, $element, !in_array($elementType,  [Vars::ITEM_TYPE_DEFENSE, Vars::ITEM_TYPE_FLEET]), false, false);
		$cost = $cost['metal'] + $cost['crystal'];

		if ($elementType == Vars::ITEM_TYPE_BUILING)
		{
			$time = ($cost / $config->game->get('game_speed')) * (1 / ($planet->getBuildLevel('nano_factory') + 1)) * pow(0.5, $planet->getBuildLevel('robot_factory'));
			$time = floor($time * 3600 * $user->bonusValue('time_building'));
		}
		elseif (in_array($elementType,  [Vars::ITEM_TYPE_TECH, Vars::ITEM_TYPE_TECH_FLEET]))
		{
			if (isset($planet->spaceLabs) && count($planet->spaceLabs))
			{
				$lablevel = 0;

				foreach ($planet->spaceLabs as $Levels)
				{
					$req = Vars::getItemRequeriments($element);

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
	static function GetElementPrice ($cost, Planet $planet)
	{
		$array = [
			'metal' 	=> [_getText('Metal'), 'metall'],
			'crystal' 	=> [_getText('Crystal'), 'kristall'],
			'deuterium' => [_getText('Deuterium'), 'deuterium'],
			'energy_max'=> [_getText('Energy'), 'energie']
		];

		$uri = Di::getDefault()->getShared('url')->getBaseUri();

		$text = "";

		foreach ($array as $ResType => $ResTitle)
		{
			if (isset($cost[$ResType]) && $cost[$ResType] != 0)
			{
				$text .= "<div><img src='".$uri."assets/images/skin/s_" . $ResTitle[1] . ".png' align=\"absmiddle\" class=\"tooltip\" data-content='" . $ResTitle[0] . "'>";

				if ($cost[$ResType] > $planet->{$ResType})
					$text .= "<span class=\"resNo tooltip\" data-content=\"необходимо: ".Helpers::pretty_number($cost[$ResType] - $planet->{$ResType})."\">" . Helpers::pretty_number($cost[$ResType]) . "</span> ";
				else
					$text .= "<span class=\"resYes\">" . Helpers::pretty_number($cost[$ResType]) . "</span> ";

				$text .= "</div>";
			}
		}

		return $text;
	}

	/**
	 * @param $user User
	 * @param $planet Planet
	 * @param $element
	 * @param bool $incremental
	 * @param bool $destroy
	 * @param bool $withBonus
	 * @return array
	 */
	static function GetBuildingPrice (User $user, Planet $planet, $element, $incremental = true, $destroy = false, $withBonus = true)
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

		foreach (['metal', 'crystal', 'deuterium', 'energy_max'] as $ResType)
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
					case Vars::ITEM_TYPE_TECH_FLEET:
						$cost[$ResType] *= $user->bonusValue('res_levelup');
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
	static function GetNextProduction ($Element, $Level, Planet $planet)
	{
		$Res = [];

		$resFrom = $planet->getProductionLevel($Element, ($Level + 1));

		$Res['m'] = $resFrom['metal'];
		$Res['c'] = $resFrom['crystal'];
		$Res['d'] = $resFrom['deuterium'];
		$Res['e'] = $resFrom['energy'];

		$resTo = $planet->getProductionLevel($Element, $Level);

		$Res['m'] -= $resTo['metal'];
		$Res['c'] -= $resTo['crystal'];
		$Res['d'] -= $resTo['deuterium'];
		$Res['e'] -= $resTo['energy'];

		$text = '';

		if ($Res['m'] != 0)
			$text .= "<br>Металл: <span class=" . (($Res['m'] > 0) ? 'positive' : 'negative') . ">" . (($Res['m'] > 0) ? '+' : '') . $Res['m'] . "</span>";

		if ($Res['c'] != 0)
			$text .= "<br>Кристалл:  <span class=" . (($Res['c'] > 0) ? 'positive' : 'negative') . ">" . (($Res['c'] > 0) ? '+' : '') . $Res['c'] . "</span>";

		if ($Res['d'] != 0)
			$text .= "<br>Дейтерий:  <span class=" . (($Res['d'] > 0) ? 'positive' : 'negative') . ">" . (($Res['d'] > 0) ? '+' : '') . $Res['d'] . "</span>";

		if ($Res['e'] != 0)
			$text .= "<br>Энергия:  <span class=" . (($Res['e'] > 0) ? 'positive' : 'negative') . ">" . (($Res['e'] > 0) ? '+' : '') . $Res['e'] . "</span>";

		return $text;
	}

	/**
	 * @param $element int
	 * @param $count int
	 * @param $user User
	 * @return mixed
	 */
	static function GetElementRessources ($element, $count, User $user)
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

		return $ResType;
	}

	/**
	 * @param $element int
	 * @param $planet Planet
	 * @param $user User
	 * @return float|int
	 */
	static function GetMaxConstructibleElements ($element, Planet $planet, User $user)
	{
		$maxElements = -1;

		$price = Vars::getItemPrice($element);

		foreach ($price as $resType => $resCount)
		{
			if (in_array($resType, ['metal', 'crystal', 'deuterium', 'energy_max']) && $resCount != 0)
			{
				$count = 0;

				switch (Vars::getItemType($element))
				{
					case Vars::ITEM_TYPE_FLEET:
						$count = round($resCount * $user->bonusValue('res_fleet'));
						break;
					case Vars::ITEM_TYPE_DEFENSE:
						$count = round($resCount * $user->bonusValue('res_defence'));
						break;
				}

				$count = floor($planet->{$resType} / $count);

				if ($maxElements < 0)
					$maxElements = $count;
				elseif ($maxElements > $count)
					$maxElements = $count;
			}
		}

		if (isset($price['max']) && $maxElements > $price['max'])
			$maxElements = $price['max'];

		return $maxElements;
	}
}