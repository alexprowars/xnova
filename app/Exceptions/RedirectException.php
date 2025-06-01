<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

class RedirectException extends Exception
{
	public function __construct(protected $url = '', $message = '', protected $code = 301)
	{
		if (empty($this->url)) {
			$this->url = Route::current()->uri();
		}

		if (!$this->url) {
			throw new $this(get_class($this) . ': Unknown $url parameter');
		}

		parent::__construct($message);
	}

	public function render()
	{
		if (empty($this->getMessage())) {
			return redirect()->away($this->url);
		}

		return new JsonResponse([
			'code' => $this->getCode(),
			'messages' => $this->getMessage(),
			'redirect' => $this->url,
		], $this->getCode());
	}
}
