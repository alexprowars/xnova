<?php

namespace App\Http\Requests;

use App\Rules\CurrentPasswordRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
	public function rules()
	{
		return [
			'current_password' => ['required', new CurrentPasswordRule()],
			'password' => ['required', Password::min(6), 'confirmed'],
		];
	}

	public function messages()
	{
		return [
			'password.confirmed' => 'Bвeдeнныe пapoли нe coвпaдaют',
		];
	}
}
