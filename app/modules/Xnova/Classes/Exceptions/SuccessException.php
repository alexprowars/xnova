<?php

namespace Xnova\Exceptions;

class SuccessException extends MainException
{
	public function __construct ($message = '')
	{
		parent::__construct($message);
	}
}