<?php

namespace App\Http\Controllers;

use App\Construction;
use App\Controller;
use App\Exceptions\PageException;

class ResearchController extends Controller
{
	public function index()
	{
		if ($this->user->isVacation()) {
			throw new PageException('Нет доступа!');
		}

		return response()->state((new Construction($this->user, $this->planet))->pageResearch());
	}
}
