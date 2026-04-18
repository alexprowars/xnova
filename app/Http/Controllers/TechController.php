<?php

namespace App\Http\Controllers;

use App\Engine\EntityFactory;
use App\Engine\Enums\ItemType;
use App\Exceptions\Exception;
use App\Facades\Vars;

class TechController extends Controller
{
	public function index(): array
	{
		$groups = [[
			'title' => __('main.tech.0'),
			'items' => array_filter(Vars::getItemsByType(ItemType::BUILDING), fn($item) => $item < 40),
		], [
			'title' => __('main.tech.40'),
			'items' => array_filter(Vars::getItemsByType(ItemType::BUILDING), fn($item) => $item > 40),
		], [
			'title' => __('main.tech.100'),
			'items' => Vars::getItemsByType(ItemType::TECH),
		], [
			'title' => __('main.tech.200'),
			'items' => Vars::getItemsByType(ItemType::FLEET),
		], [
			'title' => __('main.tech.400'),
			'items' => Vars::getItemsByType(ItemType::DEFENSE),
		]];

		$items = [];

		foreach ($groups as $group) {
			$row = [
				'title' => $group['title'],
				'items' => [],
			];

			foreach ($group['items'] as $item) {
				$itemRow = [
					'id' => $item,
					'name' => __('main.tech.' . $item),
					'required' => null,
				];

				$requeriments = Vars::getItemRequirements($item);

				foreach ($requeriments as $resClass => $level) {
					if ($resClass != 700) {
						$type = Vars::getItemType($resClass);

						if ($type == ItemType::TECH && $this->user->getTechLevel($resClass) >= $level) {
							$itemRow['required'] .= '<span class="positive">';
						} elseif ($type == ItemType::BUILDING && $this->planet->getLevel($resClass) >= $level) {
							$itemRow['required'] .= '<span class="positive">';
						} else {
							$itemRow['required'] .= '<span class="negative">';
						}

						$itemRow['required'] .= __('main.tech.' . $resClass) . " (" . __('main.level') . " " . $level;

						if ($type == ItemType::TECH && $this->user->getTechLevel($resClass) < $level) {
							$minus = $level - $this->user->getTechLevel($resClass);
							$itemRow['required'] .= ' + <b>' . $minus . '</b>';
						} elseif ($type == ItemType::BUILDING && $this->planet->getLevel($resClass) < $level) {
							$minus = $level - $this->planet->getLevel($resClass);
							$itemRow['required'] .= ' + <b>' . $minus . '</b>';
						}
					} else {
						$itemRow['required'] .= __('main.tech.' . $resClass) . ' (';

						if ($this->user->race != $level) {
							$itemRow['required'] .= '<span class="negative">' . __('main.race.' . $level);
						} else {
							$itemRow['required'] .= '<span class="positive">' . __('main.race.' . $level);
						}
					}

					$itemRow['required'] .= ')</span><br>';
				}

				$row['items'][] = $itemRow;
			}

			$items[] = $row;
		}

		return $items;
	}

	public function info(int $id): array
	{
		if (!Vars::getName($id)) {
			throw new Exception('Элемент не существует');
		}

		$entity = EntityFactory::get($id, 1, $this->planet);

		$result = [
			'id' => $id,
			'name' => __('main.tech.' . $id),
			'code' => Vars::getName($id),
			'level' => $this->user->getTechLevel($id) ?: $this->planet->getLevel($id),
			'available' => $entity->isAvailable(),
			'requirments' => Vars::getItemRequirements($id),
			'items' => [],
		];

		$storage = Vars::getStorage();

		foreach ($storage['resource'] as $element => $code) {
			$item = [
				'id' => $element,
				'name' => __('main.tech.' . $element),
				'requirments' => [],
			];

			if (isset($storage['requeriments'][$element]) && count($storage['requeriments'][$element])) {
				foreach ($storage['requeriments'][$element] as $ids => $level) {
					$item['requirments'][] = [
						'id' => $ids,
						'name' => __('main.tech.' . $ids),
						'current' => $this->user->getTechLevel($ids) ?: $this->planet->getLevel($ids),
						'level' => $level,
						'queue' => -1,
					];
				}
			}

			$result['items'][] = $item;
		}

		return $result;
	}
}
