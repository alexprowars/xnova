<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class Exception extends \Exception
{
	public function __construct(string $message = '', int $code = 500)
	{
		parent::__construct($message, $code);
	}

	public function render(): JsonResponse
	{
		$result = [
			'code' => $this->getCode(),
			'message' => $this->getMessage(),
		];

		if (!app()->isProduction()) {
			$result['trace'] = $this->getTrace();
		}

		return new JsonResponse($result, $this->getCode());
	}
}
