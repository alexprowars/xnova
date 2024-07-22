<?php

namespace App\Http\Controllers;

use App\Format;
use App\Settings;
use Illuminate\Support\Facades\Date;

class RecordsController extends Controller
{
	public function index(Settings $settings)
	{
		$RecordsArray = [];

		if (file_exists(base_path('bootstrap/cache/CacheRecords.php'))) {
			require_once(base_path('bootstrap/cache/CacheRecords.php'));
		}

		$Builds = [];
		$MoonsBuilds = [];
		$Techno = [];
		$Fleet = [];
		$Defense = [];

		foreach ($RecordsArray as $ElementID => $ElementIDArray) {
			if (($ElementID >= 1 && $ElementID <= 39) || $ElementID == 44) {
				$Builds[__('main.tech.' . $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			} elseif ($ElementID >= 41 && $ElementID <= 99 && $ElementID != 44) {
				$MoonsBuilds[__('main.tech.' . $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			} elseif ($ElementID >= 101 && $ElementID <= 199) {
				$Techno[__('main.tech.' . $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			} elseif ($ElementID >= 201 && $ElementID <= 399) {
				$Fleet[__('main.tech.' . $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			} elseif ($ElementID >= 401 && $ElementID <= 599) {
				$Defense[__('main.tech.' . $ElementID)] = [
					'winner' => ($ElementIDArray['maxlvl'] != 0) ? $ElementIDArray['username'] : '-',
					'count' => ($ElementIDArray['maxlvl'] != 0) ? Format::number($ElementIDArray['maxlvl']) : '-',
				];
			}
		}

		$Records = [
			'Постройки' => $Builds,
			'Лунные постройки' => $MoonsBuilds,
			'Исследования' => $Techno,
			'Флот' => $Fleet,
			'Оборона' => $Defense,
		];

		$parse = [
			'items' => $Records,
			'update' => Date::createFromTimestamp($settings->statUpdate, config('app.timezone'))->utc()->toAtomString(),
		];

		return response()->state($parse);
	}
}
