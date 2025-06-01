<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class Exception extends \Exception
{
	public function __construct(string $message = '', int $code = 500)
	{
		parent::__construct($message, $code);
	}

	public function render()
	{
		return new JsonResponse([
			'code' => $this->getCode(),
			'message' => $this->getMessage(),
		], $this->getCode());
	}
}
