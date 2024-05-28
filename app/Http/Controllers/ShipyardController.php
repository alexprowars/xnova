<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Construction;
use App\Controller;
use App\Exceptions\PageException;

class ShipyardController extends Controller
{
	public function index()
	{
		if ($this->user->isVacation()) {
			throw new PageException('Нет доступа!');
		}

		$construction = new Construction($this->user, $this->planet);
		$parse = $construction->pageShipyard('fleet');

		$parse['mode'] = Route::current()->getName();
		$parse['queue'] = $construction->queueList();

		return response()->state($parse);
	}
}
