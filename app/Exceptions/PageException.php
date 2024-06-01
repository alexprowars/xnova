<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class PageException extends Exception
{
	public function __construct($message = '', protected $url = null)
	{
		parent::__construct($message);
	}

	public function render()
	{
		return new JsonResponse(['error' => [
			'message' => $this->getMessage(),
			'redirect' => $this->url,
			'timeout' => 5,
		]], 403);
	}
}
