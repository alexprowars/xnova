<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class Exception extends \Exception
{
	public function render()
	{
		$type = 'error';

		if ($this instanceof NoticeException) {
			$type = 'notice';
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
