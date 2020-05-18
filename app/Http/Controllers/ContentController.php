<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Http\Request;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Models\Content;

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
