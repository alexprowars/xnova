<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Xnova\Controller;

class HallController extends Controller
{
	public function index ()
	{
		$type = (int) Request::input('type', 0);

		$parse = [];
		$parse['type'] = $type;
		$parse['hall'] = [];

		$halls = DB::table('hall')
			->where('time', '<', time() - 3600)
			->where('sab', $type)
			->orderBy('debris', 'DESC')
			->limit(50)->get();

		$time = 0;

		foreach ($halls as $hall)
		{
			$parse['hall'][] = $hall->toArray();

			if ($time < $hall->time)
				$time = $hall->time;
		}

		$parse['time'] = $time;

		$this->setTitle('Зал славы');
		$this->showTopPanel(false);

		return $parse;
	}
}