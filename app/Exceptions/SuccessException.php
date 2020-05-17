<?php

namespace Xnova\Exceptions;

class SuccessException extends Exception
{
	public function __construct($message = '')
	{
		parent::__construct($message);
	}
}
