<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

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
