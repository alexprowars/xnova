<?php

namespace Xnova\Exceptions;

class RedirectException extends MessageException
{
	protected $url = '';
	protected $timeout = 5;

	public function __construct ($message = '', $title = '', $url = '', $timeout = 5)
	{
		if (!$url)
			throw new $this(get_class($this).': Unknown $url parameter');

		$this->url = $url;

		if ($timeout > 0)
			$this->timeout = (int) $timeout;

		parent::__construct($message, $title, $url, $timeout);
	}
}