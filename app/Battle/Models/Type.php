<?php

namespace App\Battle\Models;

class Type
{
	private $id;
	private $count;

	public function __construct($id, $count)
	{
		$this->id = $id;
		$this->count = $count;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getCount()
	{
		return $this->count;
	}

	public function increment($number)
	{
		$this->count += $number;
	}

	public function decrement($number)
	{
		$this->count -= $number;
	}

	public function setCount($number)
	{
		$this->count = $number;
	}

	public function isEmpty()
	{
		return $this->count == 0;
	}

	public function cloneMe()
	{
		return new Type($this->id, $this->count);
	}
}
