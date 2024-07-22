<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class Exception extends \Exception
{
	public function render()
	{
		$type = 'error';

		return new JsonResponse([
			'error' => [
				'type' => $type,
				'code' => $this->getCode(),
				'message' => $this->getMessage(),
			],
		], 403);
	}
}
