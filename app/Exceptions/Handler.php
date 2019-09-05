<?php

namespace Xnova\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
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
    	if ($exception instanceof NotFoundHttpException)
		{
			return new JsonResponse([
				'status' => false,
				'data' => 'Страница не найдена',
			], 404);
		}

        return parent::render($request, $exception);
    }

	protected function unauthenticated ($request, AuthenticationException $exception)
	{
		return redirect()->guest('');
	}
}
