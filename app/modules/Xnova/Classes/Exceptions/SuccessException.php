<?php

namespace Xnova\Exceptions;

class SuccessException extends MainException
{
	public function __construct ($message = '', $title = '')
	{
		parent::__construct($message, $title, [
			'type' => MainException::SUCCESS
		]);
	}
}