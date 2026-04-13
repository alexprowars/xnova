<?php

namespace App\Http\Controllers;

use App\Models\HallOfFame;
use Illuminate\Http\Request;

class HallController extends Controller
{
	public function index(Request $request): array
	{
		$type = $request->input('type', 'single');

		$result = [];
		$result['type'] = $type;
		$result['items'] = [];

		$items = HallOfFame::query()
			->where('date', '<', now()->subHour())
			->where('type', $type)
			->orderByDesc('debris')
			->limit(50)
			->get();

		foreach ($items as $item) {
			$result['items'][] = $item
				->only(['id', 'report_id', 'title', 'won', 'date']);
		}

		$result['last'] = $items->sortByDesc('date')->first()->id ?? null;

		return $result;
	}
}
