<?php

namespace App\Engine\Fleet\CombatEngine\Utils;

use Iterator;
use ReturnTypeWillChange;

class IterableIterator implements Iterator
{
	protected array $array = [];

	#[ReturnTypeWillChange]
	public function rewind()
	{
		reset($this->array);
	}

	#[ReturnTypeWillChange]
	public function current()
	{
		return current($this->array);
	}

	#[ReturnTypeWillChange]
	public function key()
	{
		return key($this->array);
	}

	public function next(): void
	{
		next($this->array);
	}

	#[ReturnTypeWillChange]
	public function valid()
	{
		return $this->current() !== false;
	}

	public function getIterator()
	{
		return $this->array;
	}
}
