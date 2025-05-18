<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Facades\Vars;

class SimController extends Controller
{
	public function index($data = '')
	{
		$data = explode(";", $data);

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

		foreach ($data as $row) {
			if ($row != '') {
				$Element = explode(",", $row);
				$Count = explode("!", $Element[1]);

				if (isset($Count[1])) {
					$parse['slots']['defenders'][0][$Element[0]] = ['c' => $Count[0], 'l' => $Count[1]];
				}
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
