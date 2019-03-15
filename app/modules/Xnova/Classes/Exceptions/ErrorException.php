<?php

namespace Xnova\Exceptions;

class ErrorException extends MainException
{
	public function __construct ($message = '')
	{
		parent::__construct($message);
	}
}