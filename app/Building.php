<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova;

use Illuminate\Support\Facades\Config;

class Building
{
	public static function checkTechnologyRace(User $user, $element)
	{
		$requeriments = Vars::getItemRequirements($element);

		if (!count($requeriments)) {
			return true;
		}

		foreach ($requeriments as $reqElement => $level) {
			if ($reqElement == 700 && $user->race != $level) {
				return false;
			}
		}

		return true;
	}

	public static function checkLabSettingsInQueue(Planet $planet)
	{
		$queueManager = new Queue($planet->id_owner, $planet);

		if ($queueManager->getCount($queueManager::TYPE_BUILDING)) {
			$BuildQueue = $queueManager->get($queueManager::TYPE_BUILDING);

			if ($BuildQueue[0]->object_id == 31 && Config::get('settings.BuildLabWhileRun', 0) != 1) {
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	public static function getTechTree($element, User $user, Planet $planet)
	{
		$result = '';

		$requirements = Vars::getItemRequirements($element);

		if (!count($requirements)) {
			return $result;
		}

		foreach ($requirements as $reqId => $level) {
			$minus = 0;

			if ($reqId != 700) {
				$elementType = Vars::getItemType($reqId);

				if ($elementType === Vars::ITEM_TYPE_TECH && $user->getTechLevel($reqId) >= $level) {
					continue;
				} elseif ($elementType == Vars::ITEM_TYPE_BUILING && $planet->getBuildLevel($reqId) >= $level) {
					continue;
				}

				if ($elementType == Vars::ITEM_TYPE_TECH && $user->getTechLevel($reqId) < $level) {
					$minus = $level - $user->getTechLevel($reqId);
				} elseif ($elementType == Vars::ITEM_TYPE_BUILING && $planet->getBuildLevel($reqId) < $level) {
					$minus = $level - $planet->getBuildLevel($reqId);
				}
			} else {
				if ($user->race == $level) {
					continue;
				}

				$level = __('main.race.' . $user->race);
				$minus = __('main.race.' . $level);
			}

			$result .= '<div><span class="negative">' . __('main.tech.' . $reqId) . ' ' . $level . ($minus != 0 ? ' (' . $minus . ')' : '') . '</span></div>';
		}

		return $result;
	}

	/**
	 * @param int $Element
	 * @param int $Level
	 * @param Planet $planet
	 * @return string
	 */
	public static function getNextProduction($Element, $Level, Planet $planet)
	{
		if (!in_array($Element, Vars::getItemsByType('prod'))) {
			return '';
		}

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

		if ($Res['m'] != 0) {
			$text .= '<div class="building-effects-row"><span class="sprite skin_s_metal" title="Металл"></span> <span class=' . (($Res['m'] > 0) ? 'positive' : 'negative') . ">" . abs($Res['m']) . '</span></div>';
		}

		if ($Res['c'] != 0) {
			$text .= '<div class="building-effects-row"><span class="sprite skin_s_crystal" title="Кристалл"></span> <span class=' . (($Res['c'] > 0) ? 'positive' : 'negative') . ">" . abs($Res['c']) . '</span></div>';
		}

		if ($Res['d'] != 0) {
			$text .= '<div class="building-effects-row"><span class="sprite skin_s_deuterium" title="Дейтерий"></span> <span class=' . (($Res['d'] > 0) ? 'positive' : 'negative') . ">" . abs($Res['d']) . '</span></div>';
		}

		if ($Res['e'] != 0) {
			$text .= '<div class="building-effects-row"><span class="sprite skin_s_energy" title="Энергия"></span> <span class=' . (($Res['e'] > 0) ? 'positive' : 'negative') . ">" . abs($Res['e']) . '</span></div>';
		}

		return $text;
	}
}
