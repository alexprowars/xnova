<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use InertiaUI\Modal\Modal;

class IndexController extends Controller
{
	public function index()
	{
		return Inertia::render('Index', [
			'bodyClass' => 'index-page',
		]);
	}

	public function remind(Request $request)
	{
		$component = $request->hasHeader(Modal::HEADER_MODAL)
			? 'Index/RemindModal' : 'Index/Remind';

		return Inertia::modal($component)
			->baseRoute('remind');
	}
}
