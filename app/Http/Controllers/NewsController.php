<?php

namespace App\Http\Controllers;

class NewsController extends Controller
{
	public function index()
	{
		$items = [];

		foreach (__('news.news') as $a => $b) {
			$items[] = [
				'title' => $a,
				'text' => nl2br($b)
			];
		}

		return $items;
	}
}
