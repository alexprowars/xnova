<?php
namespace App;

use App\Models\Planet;
use App\Models\User;
use Phalcon\Mvc\User\Component;

class Building extends Component
{
	/**
	 * @param  $CurrentUser
	 * @param  $CurrentPlanet
	 * @param  $Element
	 * @param bool $Incremental
	 * @param bool $ForDestroy
	 * @return bool
	 */
	static function IsElementBuyable (User $CurrentUser, Planet $CurrentPlanet, $Element, $Incremental = true, $ForDestroy = false)
	{
		$RetValue = true;

		$cost = self::GetBuildingPrice($CurrentUser, $CurrentPlanet, $Element, $Incremental, $ForDestroy);

		foreach ($cost AS $ResType => $ResCount)
		{
			if (!isset($CurrentPlanet[$ResType]) || $ResCount > $CurrentPlanet[$ResType])
			{
				$RetValue = false;
				break;
			}
		}

		return $RetValue;
	}

	static function IsTechnologieAccessible (User $user, Planet $planet, $Element)
	{
		if (isset($requeriments[$Element]))
		{
			$enabled = true;
			foreach ($requeriments[$Element] as $ReqElement => $EleLevel)
			{
				if ($ReqElement == 700 && $user[$resource[$ReqElement]] != $EleLevel)
				{
					return false;
				}
				elseif (isset($user[$resource[$ReqElement]]) && $user[$resource[$ReqElement]] >= $EleLevel)
				{
					// break;
				}
				elseif (isset($planet[$resource[$ReqElement]]) && $planet[$resource[$ReqElement]] >= $EleLevel)
				{
					$enabled = true;
				}
				elseif (isset($planet['planet_type']) && $planet['planet_type'] == 5 && ($Element == 43 || $Element == 502 || $Element == 503) && ($ReqElement == 21 || $ReqElement == 41))
				{
					$enabled = true;
				}
				else
				{
					return false;
				}
			}
			return $enabled;
		}
		else
			return true;
	}

	static function checkTechnologyRace (User $user, $Element)
	{
		global $requeriments, $resource;

		if (isset($requeriments[$Element]))
		{
			foreach ($requeriments[$Element] as $ReqElement => $EleLevel)
			{
				if ($ReqElement == 700 && $user[$resource[$ReqElement]] != $EleLevel)
				{
					return false;
				}
			}

			return true;
		}
		else
			return true;
	}

	static function CheckLabSettingsInQueue ($CurrentPlanet)
	{
		$queueManager = new Queue($CurrentPlanet['queue']);

		if ($queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING))
		{
			$BuildQueue = $queueManager->get($queueManager::QUEUE_TYPE_BUILDING);

			if ($BuildQueue[0]['i'] == 31 && core::getConfig('BuildLabWhileRun', 0) != 1)
				$return = false;
			else
				$return = true;
		}
		else
			$return = true;

		return $return;
	}

	static function getTechTree ($Element)
	{
		$result = '';

		if (isset($requeriments[$Element]))
		{
			$result = "";

			foreach ($requeriments[$Element] as $ResClass => $Level)
			{
				if ($ResClass != 700)
				{
					if (isset(app::$user->data[$resource[$ResClass]]) && app::$user->data[$resource[$ResClass]] >= $Level)
					{
						$result .= "<span class=\"positive\">";
					}
					elseif (isset(app::$planetrow->data[$resource[$ResClass]]) && app::$planetrow->data[$resource[$ResClass]] >= $Level)
					{
						$result .= "<span class=\"positive\">";
					}
					else
					{
						$result .= "<span class=\"negative\">";
					}
					$result .= _getText('tech', $ResClass) . " (" . _getText('level') . " " . $Level . "";

					if (isset(app::$user->data[$resource[$ResClass]]) && app::$user->data[$resource[$ResClass]] < $Level)
					{
						$minus = $Level - app::$user->data[$resource[$ResClass]];
						$result .= " + <b>" . $minus . "</b>";
					}
					elseif (isset(app::$planetrow->data[$resource[$ResClass]]) && app::$planetrow->data[$resource[$ResClass]] < $Level)
					{
						$minus = $Level - app::$planetrow->data[$resource[$ResClass]];
						$result .= " + <b>" . $minus . "</b>";
					}
				}
				else
				{
					$result .= _getText('tech', $ResClass) . " (";

					if (app::$user->data['race'] != $Level)
						$result .= "<span class=\"negative\">" . _getText('race', $Level);
					else
						$result .= "<span class=\"positive\">" . _getText('race', $Level);
				}

				$result .= ")</span><br>";
			}
		}

		return $result;
	}

	/**
	 * @param  $user User
	 * @param  $planet Planet
	 * @param  $Element integer
	 * @return int
	 */
	static function GetBuildingTime (User $user, Planet $planet, $Element)
	{
		global $resource, $reslist;

		$time = 0;

		$cost = self::GetBuildingPrice($user, $planet, $Element, !(in_array($Element, $reslist['defense']) || in_array($Element, $reslist['fleet'])), false, false);
		$cost = $cost['metal'] + $cost['crystal'];

		if (in_array($Element, $reslist['build']))
		{
			$time = ($cost / core::getConfig('game_speed')) * (1 / ($planet[$resource['14']] + 1)) * pow(0.5, $planet[$resource['15']]);
			$time = floor($time * 3600 * $user->bonusValue('time_building'));
		}
		elseif (in_array($Element, $reslist['tech']) || in_array($Element, $reslist['tech_f']))
		{
			if (isset($planet['spaceLabs']) && count($planet['spaceLabs']))
			{
				$lablevel = 0;

				global $requeriments;

				foreach ($planet['spaceLabs'] as $Levels)
				{
					if (!isset($requeriments[$Element][31]) || $Levels >= $requeriments[$Element][31])
						$lablevel += $Levels;
				}
			}
			else
				$lablevel = $planet[$resource['31']];

			$time = ($cost / core::getConfig('game_speed')) / (($lablevel + 1) * 2);
			$time = floor($time * 3600 * $user->bonusValue('time_research'));
		}
		elseif (in_array($Element, $reslist['defense']))
		{
			$time = ($cost / core::getConfig('game_speed')) * (1 / ($planet[$resource['21']] + 1)) * pow(1 / 2, $planet[$resource['15']]);
			$time = floor($time * 3600 * $user->bonusValue('time_defence'));
		}
		elseif (in_array($Element, $reslist['fleet']))
		{
			$time = ($cost / core::getConfig('game_speed')) * (1 / ($planet[$resource['21']] + 1)) * pow(1 / 2, $planet[$resource['15']]);
			$time = floor($time * 3600 * $user->bonusValue('time_fleet'));
		}

		if ($time < 1)
			$time = 1;

		return $time;
	}

	/**
	 * @param $cost array
	 * @param  $planet Planet
	 * @return string
	 */
	static function GetElementPrice ($cost, Planet $planet)
	{
		$array = array(
			'metal' 	=> array(_getText('Metal'), 'metall'),
			'crystal' 	=> array(_getText('Crystal'), 'kristall'),
			'deuterium' => array(_getText('Deuterium'), 'deuterium'),
			'energy_max'=> array(_getText('Energy'), 'energie')
		);

		$text = "";

		foreach ($array as $ResType => $ResTitle)
		{
			if (isset($cost[$ResType]) && $cost[$ResType] != 0)
			{
				$text .= "<div><img src='" . DPATH . "images/s_" . $ResTitle[1] . ".png' align=\"absmiddle\" class=\"tooltip\" data-tooltip-content='" . $ResTitle[0] . "'>";

				if ($cost[$ResType] > $planet[$ResType])
				{
					$text .= "<span class=\"resNo tooltip\" data-tooltip-content=\"необходимо: ".strings::pretty_number($cost[$ResType] - $planet[$ResType])."\">" . strings::pretty_number($cost[$ResType]) . "</span> ";
				}
				else
				{
					$text .= "<span class=\"resYes\">" . strings::pretty_number($cost[$ResType]) . "</span> ";
				}
				$text .= "</div>";
			}
		}

		return $text;
	}

	/**
	 * @param $user User
	 * @param $planet Planet
	 * @param $Element
	 * @param bool $Incremental
	 * @param bool $ForDestroy
	 * @param bool $withBonus
	 * @return array
	 */
	static function GetBuildingPrice (User $user, Planet $planet, $Element, $Incremental = true, $ForDestroy = false, $withBonus = true)
	{
		global $pricelist, $resource, $reslist;

		if ($Incremental)
			$level = (isset($planet[$resource[$Element]])) ? $planet[$resource[$Element]] : $user->data[$resource[$Element]];
		else
			$level = 0;

		$array 	= array('metal', 'crystal', 'deuterium', 'energy_max');
		$cost 	= array();

		foreach ($array as $ResType)
		{
			if (!isset($pricelist[$Element][$ResType]))
				continue;

			if ($Incremental)
				$cost[$ResType] = floor($pricelist[$Element][$ResType] * pow($pricelist[$Element]['factor'], $level));
			else
				$cost[$ResType] = floor($pricelist[$Element][$ResType]);

			if ($withBonus)
			{
				if (in_array($Element, $reslist['build']))
					$cost[$ResType] = round($cost[$ResType] * $user->bonusValue('res_building'));
				elseif (in_array($Element, $reslist['tech']))
					$cost[$ResType] = round($cost[$ResType] * $user->bonusValue('res_research'));
				elseif (in_array($Element, $reslist['tech_f']))
					$cost[$ResType] = round($cost[$ResType] * $user->bonusValue('res_levelup'));
				elseif (in_array($Element, $reslist['fleet']))
					$cost[$ResType] = round($cost[$ResType] * $user->bonusValue('res_fleet'));
				elseif (in_array($Element, $reslist['defense']))
					$cost[$ResType] = round($cost[$ResType] * $user->bonusValue('res_defence'));
			}

			if ($ForDestroy)
				$cost[$ResType] = floor($cost[$ResType] / 2);
		}

		return $cost;
	}

	/**
	 * @param int $Element
	 * @param int $Level
	 * @return string
	 */
	static function GetNextProduction ($Element, $Level)
	{
		$Res = array();

		$resFrom = app::$planetrow->getProductionLevel($Element, ($Level + 1));

		$Res['m'] = $resFrom['metal'];
		$Res['c'] = $resFrom['crystal'];
		$Res['d'] = $resFrom['deuterium'];
		$Res['e'] = $resFrom['energy'];

		$resTo = app::$planetrow->getProductionLevel($Element, $Level);

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
	 * @param $Element int
	 * @param $Count int
	 * @param $user User
	 * @return mixed
	 */
	static function GetElementRessources ($Element, $Count, User $user)
	{
		global $pricelist, $reslist;

		$ResType['metal'] 		= ($pricelist[$Element]['metal'] * $Count);
		$ResType['crystal'] 	= ($pricelist[$Element]['crystal'] * $Count);
		$ResType['deuterium'] 	= ($pricelist[$Element]['deuterium'] * $Count);

		foreach ($ResType AS &$cost)
		{
			if (in_array($Element, $reslist['fleet']))
				$cost = round($cost * $user->bonusValue('res_fleet'));
			elseif (in_array($Element, $reslist['defense']))
				$cost = round($cost * $user->bonusValue('res_defence'));
		}

		return $ResType;
	}

	/**
	 * @param $Element int
	 * @param $Ressources array
	 * @param $user User
	 * @return float|int
	 */
	static function GetMaxConstructibleElements ($Element, $Ressources, User $user)
	{
		global $pricelist, $reslist;

		$MaxElements = -1;

		foreach ($pricelist[$Element] AS $need_res => $need_count)
		{
			if (in_array($need_res, array('metal', 'crystal', 'deuterium', 'energy_max')) && $need_count != 0)
			{
				$count = 0;

				if (in_array($Element, $reslist['fleet']))
					$count = round($need_count * $user->bonusValue('res_fleet'));
				elseif (in_array($Element, $reslist['defense']))
					$count = round($need_count * $user->bonusValue('res_defence'));

				$count = floor($Ressources[$need_res] / $count);

				if ($MaxElements == -1)
					$MaxElements = $count;
				elseif ($MaxElements > $count)
					$MaxElements = $count;
			}
		}

		if (isset($pricelist[$Element]['max']) && $MaxElements > $pricelist[$Element]['max'])
			$MaxElements = $pricelist[$Element]['max'];

		return $MaxElements;
	}
}

?>