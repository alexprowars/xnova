<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Controller;
use App\Exceptions\ErrorException;
use App\Models\Content;

class ContentController extends Controller
{
	public function index(string $slug)
	{
		if (empty($slug)) {
			throw new ErrorException('Страница не найдена!');
		}

		$content = Content::query()
			->where('alias', $slug)
			->first();

		if (!$content) {
			throw new ErrorException('Страница не найдена!');
		}

		return [
			'title' => $content->title,
			'html' => stripslashes($content->html)
		];
	}
}
