<?php

namespace App\Http\Controllers;

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

		return response()->state([
			'items' => $news
		]);
	}
}
