<?php

namespace Xnova\Battle\Utils;

use Iterator;

/**
 * Class IterableIterator
 * @package App\Battle\Utils
 */
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
