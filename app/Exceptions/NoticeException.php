<?php

namespace App\Exceptions;

class NoticeException extends Exception
{
	public function __construct($message = '')
	{
		parent::__construct($message);
	}
}
