<?php

namespace App\Battle\Utils;

use Iterator;

/**
 * Class Iterable
 * @package App\Battle\Utils
 */
class Iterable implements Iterator
{
	private $array;

	public function rewind()
	{
		reset($this->array);
	}

	public function current()
	{
		return current($this->array);
	}

	public function key()
	{
		return key($this->array);
	}

	public function next()
	{
		return next($this->array);
	}

	public function valid()
	{
		return $this->current() !== false;
	}

	public function getIterator()
	{
		return $this->array;
	}

}
