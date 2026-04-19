<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\ObjectsFactory;

class Vars
{
	protected array $objects;
	protected array $objectsMap;
	protected array $objectsMapFlip;

	public function __construct()
	{
		$this->init();
	}

	protected function init(): void
	{
		/** @var array<int, array> $objects */
		$objects = include(resource_path('engine/objects.php'));

		$this->objects = array_map(fn(array $item) => ObjectsFactory::make($item), $objects);
		$this->objectsMap = array_column($objects, 'code', 'id');
		$this->objectsMapFlip = array_flip($this->objectsMap);
	}

	public function getName(int $id): ?string
	{
		return $this->objectsMap[$id] ?? null;
	}

	public function getIdByName(string $name): ?int
	{
		return $this->objectsMapFlip[$name] ?? null;
	}

	public function getItemObject(int|string $itemId): ?BaseObject
	{
		if (!is_numeric($itemId)) {
			$itemId = $this->getIdByName($itemId) ?? 0;
		}

		if (!$itemId) {
			return null;
		}

		return $this->objects[$itemId] ?? null;
	}

	public function getItemType(int|string $itemId): ?ItemType
	{
		if (!is_numeric($itemId)) {
			$itemId = $this->objectsMapFlip[$itemId];
		}

		$object = $this->getItemObject($itemId);

		if ($object) {
			return $object->getType();
		}

		if (in_array($itemId, $this->getOfficiers())) {
			return ItemType::OFFICIER;
		}

		return null;
	}

	/**
	 * @param array<ItemType>|ItemType $types
	 * @return array<int>
	 */
	public function getItemsByType(array | ItemType $types): array
	{
		return array_map(fn(BaseObject $item) => $item->getId(), $this->getObjectsByType($types));
	}

	/**
	 * @param array<ItemType>|ItemType $types
	 * @return array<BaseObject>
	 */
	public function getObjectsByType(array | ItemType $types): array
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		return array_filter($this->objects, fn(BaseObject $item) => in_array($item->getType(), $types));
	}

	public function getOfficiers(): array
	{
		return [
			'geologist',
			'admiral',
			'engineer',
			'technocrat',
			'architect',
			'metaphysician',
			'mercenary',
		];
	}

	/**
	 * @return array<'metal'|'crystal'|'deuterium'>
	 */
	public function getResources(): array
	{
		return ['metal', 'crystal', 'deuterium'];
	}
}
