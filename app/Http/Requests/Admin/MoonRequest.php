<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MoonRequest extends FormRequest
{
	public function rules()
	{
		return [
			'galaxy' => 'required|numeric|galaxy',
			'system' => 'required|numeric|system',
			'planet' => 'required|numeric|planet',
			'diameter' => 'required|numeric|min:1|max:20',
			'user_id' => 'required|numeric|exists:users,id',
		];
	}

	public function messages()
	{
		return [
			'numeric' => 'Введите число',
			'required' => 'Поле ":attribute" обязательно для заполнения',
			'diameter.min' => 'Диаметр должен быть не меньше :min',
			'diameter.max' => 'Диаметр должен быть не больше :max',
			'galaxy.required' => 'Введите номер галактики',
			'galaxy.galaxy' => 'Данного номера галактики не существует',
			'system.required' => 'Введите номер системы',
			'system.system' => 'Данного номера системы не существует',
			'planet.required' => 'Введите номер планеты',
			'planet.planet' => 'Данного номера планеты не существует',
			'user_id.required' => 'Введите id пользователя',
			'user_id.exists' => 'Такого пользователя не существует',
		];
	}
}
