<?php

namespace App\Http\Controllers;

use App\Exceptions\PageException;
use App\Models\Content;

class ContentController extends Controller
{
	public function index(string $slug)
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

		return [
			'title' => $content->title,
			'html' => stripslashes($content->html)
		];
	}
}
