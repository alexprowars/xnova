<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class CurrentPasswordRule implements ValidationRule
{
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!Hash::check($value, auth()->user()->password)) {
			$fail('Heпpaвильно введен тeкyщий пapoль');
		}
	}
}
