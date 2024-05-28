<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Construction;
use App\Controller;
use App\Exceptions\PageException;

class DefenseController extends Controller
{
	public function index()
	{
		if ($this->user->isVacation()) {
			throw new PageException('Нет доступа!');
		}

		if ($this->planet->planet_type == 5) {
			$this->user->setOption('only_available', true);
		}

		$construction = new Construction($this->user, $this->planet);
		$parse = $construction->pageShipyard('defense');

		$parse['mode'] = Route::current()->getName();
		$parse['queue'] = $construction->queueList();

		return response()->state($parse);
	}
}
