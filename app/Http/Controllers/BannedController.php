<?php

namespace App\Http\Controllers;

use App\Models\Blocked;
use App\Controller;

class BannedController extends Controller
{
	public function index()
	{
		$rows = Blocked::orderByDesc('id')
			->with(['user', 'author'])
			->get();

		$items = [];

		foreach ($rows as $u) {
			$items[] = [
				'user' => [
					'id' => $u->user?->id,
					'name' => $u->user?->username,
				],
				'moderator' => [
					'id' => $u->author?->id,
					'name' => $u->author?->username,
				],
				'time' => $u->created_at?->format('c'),
				'time_end' => $u->longer?->format('c'),
				'reason' => $u->reason,
			];
		}

		return [
			'items' => $items
		];
	}
}
