<?php

namespace Xnova\Exceptions;

class ErrorException extends MainException
{
	public function __construct ($message = '', $title = '')
	{
		parent::__construct($message, $title);
	}
}