<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

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

		$this->showTopPanel(false);

		return [
			'title' => $content->title,
			'html' => stripslashes($content->html)
		];
	}
}
