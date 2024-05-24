<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class RedirectException extends Exception
{
	protected $url = '';
	protected $timeout = 5;

	public function __construct($message = '', $url = '')
	{
		if ($url == '') {
			$url = Route::current()->uri();
		}

		if (!$url) {
			throw new $this(get_class($this) . ': Unknown $url parameter');
		}

		$this->url = str_replace(Request::root(), '', rtrim(URL::to($url), '/')) . '/';
		$this->url = str_replace('/' . Route::current()->getPrefix(), '', $this->url);

		parent::__construct($message);
	}

	public function render()
	{
		if (empty($this->getMessage())) {
			return new JsonResponse([
				'redirect' => $this->url,
			]);
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
