<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models\Content;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContentController extends Controller
{
	public function index(string $slug, Request $request)
	{
		if (empty($slug)) {
			throw new PageException('Страница не найдена!');
		}

		$content = Content::query()
			->where('alias', $slug)
			->first();

		if (!$content) {
			throw new PageException('Страница не найдена!');
		}

		$result = [
			'title' => $content->title,
			'html' => stripslashes($content->html),
		];

		if (!$request->inertia() && $request->expectsJson()) {
			return response()->json($result);
		}

		return Inertia::render('Content', [
			'data' => $result,
		]);
	}
}
