<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

class RedirectException extends Exception
{
	public function __construct($message = '', protected $url = '')
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
			'messages' => [[
				'type' => 'notice',
				'text' => $this->getMessage(),
			]],
			'redirect' => $this->url,
		]);
	}
}
