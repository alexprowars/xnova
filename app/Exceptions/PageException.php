<?php

namespace Xnova\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class PageException extends Exception
{
	protected $url = false;

	public function __construct ($message = '', $url = false)
	{
		if ($url)
		{
			$this->url = str_replace(Request::root(), '', rtrim(URL::to($url), '/')).'/';
			$this->url = str_replace('/'.Route::current()->getPrefix(), '', $this->url);
		}

		parent::__construct($message);
	}

	public function render ()
	{
		return new JsonResponse([
			'message' => $this->getMessage(),
			'redirect' => $this->url,
			'timeout' => 5,
		]);
	}
}