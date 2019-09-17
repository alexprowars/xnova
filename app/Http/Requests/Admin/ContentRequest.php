<?php

namespace Xnova\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContentRequest extends FormRequest
{
	public function rules()
	{
		return [
			'title' => 'required',
			'code' => 'required',
			'html' => 'required',
		];
	}

	public function messages ()
	{
		return [
			'required' => 'Поле ":attribute" обязательно для заполнения',
		];
	}
}