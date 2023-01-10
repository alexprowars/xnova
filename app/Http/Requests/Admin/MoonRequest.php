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
			'id_owner' => 'required|numeric|exists:users,id',
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
			'id_owner.required' => 'Введите id пользователя',
			'id_owner.exists' => 'Такого пользователя не существует',
		];
	}
}
