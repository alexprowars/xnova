<?php

namespace Xnova\Exceptions;

class ErrorException extends Exception
{
	public function __construct($message = '')
	{
		parent::__construct($message);
	}
}
