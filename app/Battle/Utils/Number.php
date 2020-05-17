<?php

namespace Xnova\Battle\Utils;

class Number
{
	public $rest;
	public $result;

	public function __construct($result, $rest = 0)
	{
		$this->rest = $rest;
		$this->result = $result;
	}

	public function __toString()
	{
		return "result=$this->result;rest=$this->rest;";
	}
}
