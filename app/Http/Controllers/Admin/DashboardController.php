<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends AdminController
{
	public function __invoke()
	{
		return Inertia::render('Admin/Dashboard');
	}

	public function locale(Request $request)
	{
		$data = $request->validate(['locale' => 'required|in:ru,en']);

		$request->user()->update($data);
		$request->session()->put('locale', $data['locale']);

		return back();
	}
}
