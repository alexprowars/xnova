<?php

namespace App\Http\Controllers;

use App\Format;
use App\Settings;
use Illuminate\Support\Facades\Date;

class RecordsController extends Controller
{
	public function index(Settings $settings)
	{
		/** @var array<int, array> $recordsArray */
		$recordsArray = [];

		if (file_exists(base_path('bootstrap/cache/CacheRecords.php'))) {
			require_once(base_path('bootstrap/cache/CacheRecords.php'));
		}

		$Builds = [];
		$MoonsBuilds = [];
		$Techno = [];
		$Fleet = [];
		$Defense = [];

		foreach ($recordsArray as $entitytId => $entityData) {
			if (($entitytId >= 1 && $entitytId <= 39) || $entitytId == 44) {
				$Builds[__('main.tech.' . $entitytId)] = [
					'winner' => ($entityData['maxlvl'] != 0) ? $entityData['username'] : '-',
					'count' => ($entityData['maxlvl'] != 0) ? Format::number($entityData['maxlvl']) : '-',
				];
			} elseif ($entitytId >= 41 && $entitytId <= 99) {
				$MoonsBuilds[__('main.tech.' . $entitytId)] = [
					'winner' => ($entityData['maxlvl'] != 0) ? $entityData['username'] : '-',
					'count' => ($entityData['maxlvl'] != 0) ? Format::number($entityData['maxlvl']) : '-',
				];
			} elseif ($entitytId >= 101 && $entitytId <= 199) {
				$Techno[__('main.tech.' . $entitytId)] = [
					'winner' => ($entityData['maxlvl'] != 0) ? $entityData['username'] : '-',
					'count' => ($entityData['maxlvl'] != 0) ? Format::number($entityData['maxlvl']) : '-',
				];
			} elseif ($entitytId >= 201 && $entitytId <= 399) {
				$Fleet[__('main.tech.' . $entitytId)] = [
					'winner' => ($entityData['maxlvl'] != 0) ? $entityData['username'] : '-',
					'count' => ($entityData['maxlvl'] != 0) ? Format::number($entityData['maxlvl']) : '-',
				];
			} elseif ($entitytId >= 401 && $entitytId <= 599) {
				$Defense[__('main.tech.' . $entitytId)] = [
					'winner' => ($entityData['maxlvl'] != 0) ? $entityData['username'] : '-',
					'count' => ($entityData['maxlvl'] != 0) ? Format::number($entityData['maxlvl']) : '-',
				];
			}
		}

		$records = [
			'Постройки' => $Builds,
			'Лунные постройки' => $MoonsBuilds,
			'Исследования' => $Techno,
			'Флот' => $Fleet,
			'Оборона' => $Defense,
		];

		return [
			'items' => $records,
			'update' => Date::createFromTimestamp($settings->statUpdate, config('app.timezone'))->utc()->toAtomString(),
		];
	}
}
