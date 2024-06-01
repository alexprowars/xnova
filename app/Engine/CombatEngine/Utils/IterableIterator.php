<?php

namespace App\Engine\CombatEngine\Utils;

use Iterator;

class IterableIterator implements Iterator
{
	public function rewind()
	{
		/** @noinspection PhpUndefinedFieldInspection */
		reset($this->array);
	}

	public function current()
	{
		/** @noinspection PhpUndefinedFieldInspection */
		return current($this->array);
	}

	public function key()
	{
		/** @noinspection PhpUndefinedFieldInspection */
		return key($this->array);
	}

	public function next()
	{
		/** @noinspection PhpUndefinedFieldInspection */
		return next($this->array);
	}

	public function valid()
	{
		return $this->current() !== false;
	}

	public function getIterator()
	{
		/** @noinspection PhpUndefinedFieldInspection */
		return $this->array;
	}
}
