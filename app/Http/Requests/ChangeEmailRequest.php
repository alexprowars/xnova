<?php

namespace App\Http\Requests;

use App\Rules\CurrentPasswordRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailRequest extends FormRequest
{
	public function rules()
	{
		return [
			'password' => ['required', new CurrentPasswordRule()],
			'email' => 'required|email:rfc,dns|unique:users,email',
		];
	}

	public function messages()
	{
		return [
			'email.unique' => 'Данный email уже используется в игре.',
		];
	}
}
