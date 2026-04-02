<?php

namespace App\Engine\Fleet\CombatEngine\Models;

class Type
{
	public function __construct(protected int $id, protected int $count)
	{
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getCount(): int
	{
		return $this->count;
	}

	public function increment(int $number)
	{
		$this->count += $number;
	}

	public function decrement(int $number)
	{
		$this->count -= $number;
	}

	public function setCount(int $number)
	{
		$this->count = $number;
	}

	public function isEmpty(): bool
	{
		return $this->count == 0;
	}

	public function cloneMe(): Type
	{
		return new Type($this->id, $this->count);
	}
}
