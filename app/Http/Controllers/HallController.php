<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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

		$halls = DB::table('halls')
			->where('time', '<', time() - 3600)
			->where('sab', $type)
			->orderBy('debris', 'DESC')
			->limit(50)->get();

		$time = 0;

		foreach ($halls as $hall) {
			$parse['hall'][] = (array) $hall;

			if ($time < $hall->time) {
				$time = $hall->time;
			}
		}

		$parse['time'] = $time;

		$this->setTitle('Зал славы');
		$this->showTopPanel(false);

		return $parse;
	}
}
