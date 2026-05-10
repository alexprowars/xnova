<?php

namespace App\Exceptions;

class PageException extends \Exception
{
	public function __construct(string $message = '', int $code = 500)
	{
		parent::__construct($message, $code);
	}

	public function report(): bool
	{
		return true;
	}
}
