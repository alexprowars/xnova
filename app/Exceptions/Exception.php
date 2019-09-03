<?php

namespace Xnova\Exceptions;

use Illuminate\Http\JsonResponse;

class Exception extends \Exception
{
	public function render ()
	{
		return new JsonResponse([
			'message' => $this->getMessage(),
		]);
	}
}