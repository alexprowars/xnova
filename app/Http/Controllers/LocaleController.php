<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
	public function update(Request $request): RedirectResponse
	{
		$data = $request->validate([
			'locale' => 'required|string|in:en,ru',
		]);

		if ($user = $request->user()) {
			$user->locale = $data['locale'];
			$user->save();
		}

		$request->session()->put('locale', $data['locale']);

		return back();
	}
}
