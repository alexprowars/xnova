<?php

namespace App\Exceptions;

use App\Support\ToastType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InertiaUI\Modal\Modal;
use Symfony\Component\HttpFoundation\Response;

class Exception extends \Exception
{
	public function __construct(string $message = '', int $code = 500)
	{
		parent::__construct($message, $code);
	}

	public function render(Request $request): Response
	{
		if ((!$request->isMethod('GET') && $request->inertia() && !$request->expectsJson()) || $request->hasHeader('x-modal')) {
			toast(ToastType::ERROR, $this->getMessage());

			return back()->withErrors(['error' => $this->getMessage()]);
		}

		//if ($request->isMethod('GET') && $request->inertia() && !$request->expectsJson()) {
		////	toast(ToastType::ERROR, $this->getMessage());
//
//			return back()->withErrors($this->getMessage());
//		}

		if ($request->isMethod('GET') && !$request->expectsJson()) {
			$component = $request->hasHeader(Modal::HEADER_MODAL) ? 'Errors/Modal' : 'Errors/Page';

			return inertia()->render($component, [
				'errors' => ['error' => $this->getMessage()],
				'status' => $this->getCode(),
				'message' => $this->getMessage(),
			])->toResponse($request);
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
