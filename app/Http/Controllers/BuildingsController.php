<?php

namespace App\Http\Controllers;

use App\Construction;
use App\Controller;
use App\Exceptions\PageException;

class BuildingsController extends Controller
{
	public function index()
	{
		if ($this->user->isVacation() > 0) {
			throw new PageException('Нет доступа!');
		}

		$construction = new Construction($this->user, $this->planet);
		return $construction->pageBuilding();
	}
}
