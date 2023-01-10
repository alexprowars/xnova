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
	public function index(Request $request)
	{
		if (!$request->query('article')) {
			throw new ErrorException('Страница не найдена!');
		}

		$content = Content::query()
			->where('alias', $request->query('article'))
			->first();

		if (!$content) {
			throw new ErrorException('Страница не найдена!');
		}

		$this->setTitle($content->title);
		$this->showTopPanel(false);

		return [
			'html' => stripslashes($content->html)
		];
	}
}
