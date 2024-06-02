<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\Content;

class ContentController extends Controller
{
	public function index(string $slug)
	{
		if (empty($slug)) {
			throw new Exception('Страница не найдена!');
		}

		$content = Content::query()
			->where('alias', $slug)
			->first();

		if (!$content) {
			throw new Exception('Страница не найдена!');
		}

		return [
			'title' => $content->title,
			'html' => stripslashes($content->html)
		];
	}
}
