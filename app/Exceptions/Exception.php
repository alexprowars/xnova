<?php

namespace App\Exceptions;

use App\Support\ToastType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Exception extends \Exception
{
	public function __construct(string $message = '', int $code = 500)
	{
		parent::__construct($message, $code);
	}

	public function render(Request $request): Response
	{
		if (!$request->isMethod('GET') && $request->inertia() && !$request->expectsJson()) {
			toast(ToastType::ERROR, $this->getMessage());

			return back();
		}

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
