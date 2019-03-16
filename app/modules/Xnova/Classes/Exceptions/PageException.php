<?php

namespace Xnova\Exceptions;

use Phalcon\Di;
use Xnova\Request;

class PageException extends MainException
{
	public function __construct ($message = '', $url = false)
	{
		Request::addData('error', [
			'message' => $message,
			'redirect' => $url ? Di::getDefault()->getShared('url')->get(ltrim($url, '/')) : false,
			'timeout' => 5
		]);

		parent::__construct($message, 0);
	}
}