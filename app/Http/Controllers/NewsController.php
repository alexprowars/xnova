<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use App\Controller;

class NewsController extends Controller
{
	public function index()
	{
		$news = [];

		foreach (__('news.news') as $a => $b) {
			$news[] = [
				'title' => $a,
				'text' => nl2br($b)
			];
		}

		$this->showTopPanel(false);

		return [
			'items' => $news
		];
	}
}
