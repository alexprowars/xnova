<?php

namespace App\Exceptions;

use Inertia\Inertia;

class PageException extends \Exception
{
	public function __construct(string $message = '', int $code = 500)
	{
		//Inertia::share('errors', ['asdsa' => 'asdsad']);
		//Inertia::merge(['message' => $message])->prepend('errors');

		parent::__construct($message, $code);
	}
}
