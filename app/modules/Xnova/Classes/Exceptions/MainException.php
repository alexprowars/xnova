<?php

namespace Xnova\Exceptions;

use Phalcon\Di;
use Xnova\Request;

class MainException extends \Exception
{
	const SUCCESS = 'success';
	const ERROR = 'error';
	const REDIRECT = 'redirect';

	public function __construct ($message, $title = '', $params = [])
	{
		$url = Di::getDefault()->getShared('url');

		Request::addData('error', [
			'type' => isset($params['type']) ? $params['type'] : self::ERROR,
			'title' => $title,
			'message' => $message,
			'redirect' => isset($params['url']) ? $url->get($params['url']) : false,
			'timeout' => isset($params['timeout']) ? $params['timeout'] : 0
		]);

		parent::__construct($message, 0);
	}
}