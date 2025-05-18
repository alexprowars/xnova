<?php

namespace App\Engine\Contracts;

use App\Engine\Enums\Resources;

interface EntityInterface
{
	public function getLevel(): int;

	/**
	 * @return array<value-of<Resources>, int>
	 */
	public function getPrice(): array;
	public function getTime(): int;
	public function isAvailable(): bool;
	public function canConstruct(): bool;
}
