<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use App\Controller;
use App\Vars;

class SimController extends Controller
{
	protected $loadPlanet = true;

	/**
	 * @Route("/{data:[0-9!;,]+}{params:(/.*)*}")
	 * @param string $data
	 */
	public function index($data = '')
	{
		$data = explode(";", $data);

		$maxSlots = config('settings.maxSlotsInSim', 5);

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

		$res = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE, Vars::ITEM_TYPE_TECH]);

		foreach ($res as $id) {
			if ($this->planet->getLevel($id) > 0) {
				$parse['slots']['attackers'][0][$id] = ['c' => $this->planet->getLevel($id)];
			}

			if ($this->user->getTechLevel($id) > 0) {
				$parse['slots']['attackers'][0][$id] = ['c' => $this->user->getTechLevel($id)];
			}
		}

		$this->showTopPanel(false);

		return $parse;
	}
}
