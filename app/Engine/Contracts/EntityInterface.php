<?php

namespace App\Engine\Contracts;

interface EntityInterface
{
	public function getLevel(): int;
	public function getPrice(): array;
	public function getTime(): int;
	public function isAvailable(): bool;
	public function canConstruct(): bool;
}
