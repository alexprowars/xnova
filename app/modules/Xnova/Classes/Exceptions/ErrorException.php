<?php

namespace Xnova\Exceptions;

class ErrorException extends MessageException
{
	public function __construct ($message = '', $title = '')
	{
		parent::__construct($message, $title);
	}
}