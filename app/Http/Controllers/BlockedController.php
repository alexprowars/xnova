<?php

namespace App\Http\Controllers;

use App\Models\Blocked;

class BlockedController extends Controller
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
				'date' => $u->created_at?->utc()->toAtomString(),
				'date_end' => $u->longer?->utc()->toAtomString(),
				'reason' => $u->reason,
			];
		}

		return $items;
	}
}
