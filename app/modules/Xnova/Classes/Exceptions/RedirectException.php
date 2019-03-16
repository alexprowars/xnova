<?php

namespace Xnova\Exceptions;

use Phalcon\Di;
use Xnova\Request;

class RedirectException extends MainException
{
	protected $url = '';
	protected $timeout = 5;

	public function __construct ($message = '', $url = '')
	{
		if ($url == '')
			$url = Di::getDefault()->getShared('router')->getRewriteUri();

		if (!$url)
			throw new $this(get_class($this).': Unknown $url parameter');

		$this->url = ltrim($url, '/');

		$url = Di::getDefault()->getShared('url')->get($url);

		Request::addData('redirect', $url);

		parent::__construct($message);
	}
}