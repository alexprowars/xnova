<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use Illuminate\Support\Facades\Request;
use App\Controller;

class HallController extends Controller
{
	public function index()
	{
		$type = (int) Request::input('type', 0);

		$parse = [];
		$parse['type'] = $type;
		$parse['hall'] = [];

		$halls = Hall::query()
			->where('time', '<', now()->subHour())
			->where('sab', $type)
			->orderBy('debris', 'DESC')
			->limit(50)->get();

		$time = 0;

		foreach ($halls as $hall) {
			$parse['hall'][] = (array) $hall;

			if ($time < $hall->time->timestamp) {
				$time = $hall->time->timestamp;
			}
		}

		$parse['time'] = $time;

		return response()->state($parse);
	}
}
