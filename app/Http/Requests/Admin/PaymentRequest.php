<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
	public function rules()
	{
		return [
			'name' => 'required',
			'amount' => 'required|numeric|min:1',
		];
	}

	public function messages()
	{
		return [
			'numeric' => 'Введите число',
			'required' => 'Поле ":attribute" обязательно для заполнения',
			'name.required' => 'Введите логин игрока',
			'amount.required' => 'Введите сумму зачисления',
			'amount.min' => 'Сумма должна быть не меньше :min',
		];
	}
}
