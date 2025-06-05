<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Facades\Vars;
use App\Models\Planet;
use App\Models\User;

class Building
{
	public static function checkTechnologyRace(User $user, int $element)
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
		$BuildQueue = (new QueueManager($planet))
			->get(QueueType::BUILDING);

		if ($BuildQueue->isNotEmpty()) {
			if ($BuildQueue->first()->object_id == 31 && config('game.BuildLabWhileRun', 0) != 1) {
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	public static function getTechTree(int $element, User $user, Planet $planet): ?array
	{
		$requirements = Vars::getItemRequirements($element);

		if (!count($requirements)) {
			return null;
		}

		$result = [];

		foreach ($requirements as $reqId => $level) {
			$minus = 0;

			if ($reqId != 700) {
				$elementType = Vars::getItemType($reqId);

				if ($elementType === ItemType::TECH && $user->getTechLevel($reqId) >= $level) {
					continue;
				} elseif ($elementType == ItemType::BUILDING && $planet->getLevel($reqId) >= $level) {
					continue;
				}

				if ($elementType == ItemType::TECH && $user->getTechLevel($reqId) < $level) {
					$minus = $level - $user->getTechLevel($reqId);
				} elseif ($elementType == ItemType::BUILDING && $planet->getLevel($reqId) < $level) {
					$minus = $level - $planet->getLevel($reqId);
				}
			} else {
				if ($user->race == $level) {
					continue;
				}

				$level = __('main.race.' . $user->race);
				$minus = __('main.race.' . $level);
			}

			$result[] = [
				'id' => $reqId,
				'level' => $level,
				'diff' => $minus,
			];
		}

		return $result;
	}

	public static function getNextProduction(int $elementId, int $level, ?Planet $planet = null): ?Resources
	{
		if (!in_array($elementId, Vars::getItemsByType(ItemType::PRODUCTION))) {
			return null;
		}

		$entity = EntityFactory::get($elementId, $level + 1, $planet);

		$resources = $entity->getProduction();

		$entity->setLevel($level);

		return $resources->sub($entity->getProduction());
	}
}
