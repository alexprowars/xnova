<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
	public function rules()
	{
		return [
			'email' => 'required|email|unique:users,email',
			'password' => 'required|min:5',
			'password_confirmation' => 'required|same:password',
		];
	}

	public function messages()
	{
		return [
			'email.unique' => 'Такой e-mail уже используется',
			'password.min' => 'Пароль должен состоять как минимум из :min символов',
			'password_confirmation.same' => 'Пароли должны быть одинаковыми',
		];
	}
}
