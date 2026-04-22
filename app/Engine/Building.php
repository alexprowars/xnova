<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\ObjectsFactory;
use App\Facades\Vars;
use App\Models\Planet;
use App\Models\User;

class Building
{
	public static function checkTechnologyRace(User $user, int $element): bool
	{
		$object = ObjectsFactory::get($element);

		$requeriments = $object->getRequeriments();

		if (empty($requeriments)) {
			return true;
		}

		foreach ($requeriments as $reqElement => $level) {
			if ($reqElement == 'race' && $user->race != $level) {
				return false;
			}
		}

		return true;
	}

	public static function checkLabInQueue(Planet $planet): bool
	{
		$BuildQueue = (new QueueManager($planet))
			->get(QueueType::BUILDING);

		if ($BuildQueue->isNotEmpty()) {
			if ($BuildQueue->first()->object_id == 31 && config('game.BuildLabWhileRun', 0) != 1) {
				return true;
			}

			return false;
		}

		return false;
	}

	public static function getTechTree(BaseObject $object, User $user, Planet $planet): ?array
	{
		$requirements = $object->getRequeriments();

		if (empty($requirements)) {
			return null;
		}

		$result = [];

		foreach ($requirements as $reqId => $level) {
			$minus = 0;

			if ($reqId != 'race') {
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
				'name' => __('main.tech.' . $reqId),
				'level' => $level,
				'diff' => $minus,
			];
		}

		return $result;
	}

	public static function getNextProduction(BaseObject $object, int $level, ?Planet $planet = null): ?Resources
	{
		if (!$object->getProduction()) {
			return null;
		}

		$entity = EntityFactory::get($object->getId(), $level + 1, $planet);

		$resources = $entity->getProduction();

		if (!$resources) {
			return null;
		}

		$entity->setLevel($level);

		return $resources->sub($entity->getProduction());
	}
}
