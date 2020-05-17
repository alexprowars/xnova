<?php

namespace Xnova\Exceptions;

use Illuminate\Http\JsonResponse;

class Exception extends \Exception
{
	public function render()
	{
		$type = 'notice';

		if ($this instanceof ErrorException) {
			$type = 'error';
		} elseif ($this instanceof SuccessException) {
			$type = 'success';
		}

		$message = [
			'type' => $type,
			'text' => $this->getMessage(),
		];

		return new JsonResponse([
			'messages' => [$message],
		]);
	}
}
