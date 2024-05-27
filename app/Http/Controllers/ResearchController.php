<?php

namespace App\Http\Controllers;

use App\Construction;
use App\Controller;
use App\Exceptions\PageException;

class ResearchController extends Controller
{
	public function index()
	{
		if ($this->user->vacation) {
			throw new PageException('Нет доступа!');
		}

		$construction = new Construction($this->user, $this->planet);
		return $construction->pageResearch();
	}
}
