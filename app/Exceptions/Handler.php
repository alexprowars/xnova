<?php

namespace Xnova\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report (Exception $exception)
    {
        parent::report($exception);
    }

    public function render ($request, Exception $exception)
    {
    	if (strpos($request->path(), 'admin/') === false &&
			$exception instanceof Exception &&
				!method_exists($exception, 'render')
		)
		{
			$data = [
				'message' => $exception->getMessage(),
			];

			$debug = Config::get('app.debug');

			if ($debug)
				$data['trace'] = $exception->getTraceAsString();

			return new JsonResponse(
				[
					'status' => false,
					'data' => $data
				],
				$this->isHttpException($exception) ? $exception->getStatusCode() : 500,
				$this->isHttpException($exception) ? $exception->getHeaders() : [],
				JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
			);
		}

        return parent::render($request, $exception);
    }

	protected function unauthenticated ($request, AuthenticationException $exception)
	{
		return redirect()->guest('');
	}
}
