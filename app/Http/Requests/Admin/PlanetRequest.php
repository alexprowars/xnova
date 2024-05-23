<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlanetRequest extends FormRequest
{
	public function rules()
	{
		return [
			'name' => 'required|min:5|max:255',
			'galaxy' => 'required|numeric|galaxy',
			'system' => 'required|numeric|system',
			'planet' => 'required|numeric|planet',
			'user_id' => 'required|numeric|exists:users,id',
		];
	}

	public function messages()
	{
		return [
			'numeric' => 'Введите число',
			'required' => 'Поле ":attribute" обязательно для заполнения',
			'name.required' => 'Введите название планеты',
			'name.min' => 'Название планеты не может быть короче :min символов',
			'name.max' => 'Название планеты не может быть длиннее :max символов',
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
