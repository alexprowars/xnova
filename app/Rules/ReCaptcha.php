<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ReCaptcha implements ValidationRule
{
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!empty(config('services.recaptcha.secret_key'))) {
			$response = Http::get('https://www.google.com/recaptcha/api/siteverify', [
				'secret' => config('services.recaptcha.secret_key'),
				'response' => $value
			]);

			if (!$response->json('success', false)) {
				$fail('The google recaptcha check failed. Please try again.');
			}
		}
	}
}
