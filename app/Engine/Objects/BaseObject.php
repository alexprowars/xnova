<?php

namespace App\Engine\Objects;

use App\Engine\Enums\ItemType;
use App\Exceptions\Exception;

abstract class BaseObject
{
	public function __construct(protected array $data)
	{
	}

	public function getId(): int
	{
		return $this->data['id'];
	}

	public function getName(): string
	{
		return __('main.tech.' . $this->getId());
	}

	public function getCode(): string
	{
		return $this->data['code'];
	}

	public function getType(): ItemType
	{
		return match ($this->data['type']) {
			'building' => ItemType::BUILDING,
			'research' => ItemType::TECH,
			'fleet' => ItemType::FLEET,
			'defense' => ItemType::DEFENSE,
			default => throw new Exception('unknown type: ' . $this->data['type'])
		};
	}

	public function getPrice(): array
	{
		return $this->data['price'] ?? [];
	}

	public function getTotalPrice(bool $all = false): int
	{
		$price = $this->getPrice();

		if (empty($price)) {
			return 0;
		}

		if (!$all) {
			return $price['metal'] + $price['crystal'];
		}

		return $price['metal'] + $price['crystal'] + $price['deuterium'];
	}

	public function getRequeriments(): array
	{
		return $this->data['requeriments'] ?? [];
	}

	public function getProduction(): ?array
	{
		return $this->data['production'] ?? null;
	}
}
