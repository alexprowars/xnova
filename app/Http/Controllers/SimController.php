<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Facades\Vars;

class SimController extends Controller
{
	public function index($data = '')
	{
		if (!empty($data)) {
			$units = explode(';', $data);
		} elseif (request()->has('units')) {
			$units = explode(';', request()->get('units'));
		} else {
			$units = [];
		}

		$units = array_filter($units);

		$maxSlots = config('game.maxSlotsInSim', 5);

		$parse = [];
		$parse['slots'] = [
			'max' => $maxSlots,
			'attackers' => [
				0 => []
			],
			'defenders' => [
				0 => []
			],
		];

		$parse['tech'] = [109, 110, 111, 120, 121, 122];

		foreach ($units as $row) {
			$element = explode(',', $row);

			if (!is_numeric($element[0])) {
				$element[0] = Vars::getIdByName($element[0]);
			}

			if (isset($element[1])) {
				$parse['slots']['defenders'][0][$element[0]] = ['c' => $element[1]];
			}
		}

		$res = Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE, ItemType::TECH]);

		foreach ($res as $id) {
			if ($this->planet->getLevel($id) > 0) {
				$parse['slots']['attackers'][0][$id] = ['c' => $this->planet->getLevel($id)];
			}

			if ($this->user->getTechLevel($id) > 0) {
				$parse['slots']['attackers'][0][$id] = ['c' => $this->user->getTechLevel($id)];
			}
		}

		return $parse;
	}
}
