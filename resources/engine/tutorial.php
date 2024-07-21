<?php

use App\Engine\Fleet\Mission;

return [
	1 => [
		'required' => [],
		'task' => ['build' => [1 => 4, 2 => 2, 4 => 4]],
		'reward' => ['metal' => 1000, 'crystal' => 500],
	],
	2 => [
		'required' => ['quest' => 1],
		'task' => ['build' => [3 => 2, 14 => 2, 21 => 1, 401 => 1]],
		'reward' => ['build' => [401 => 3]],
	],
	3 => [
		'required' => ['quest' => 2],
		'task' => ['build' => [1 => 10, 2 => 7, 3 => 5]],
		'reward' => ['metal' => 5000, 'crystal' => 2500],
	],
	4 => [
		'required' => ['quest' => 3],
		'task' => ['build' => [31 => 1, 115 => 2, 202 => 1]],
		'reward' => ['deuterium' => 2000, 'credits' => 10],
	],
	5 => [
		'required' => ['quest' => 4],
		'task' => ['!planet_name' => 'главная планета', 'buddy_count' => 1, 'ally' => 1],
		'reward' => ['credits' => 10],
	],
	6 => [
		'required' => ['quest' => 5],
		'task' => ['storage' => true, 'trade' => true],
		'reward' => ['storage_rand' => 1],
	],
	7 => [
		'required' => ['quest' => 6],
		'task' => ['build' => [210 => 1], 'fleet_mission' => Mission::Spy],
		'reward' => ['build' => [210 => 5]],
	],
	8 => [
		'required' => ['quest' => 7],
		'task' => ['fleet_mission' => Mission::Expedition],
		'reward' => ['build' => [202 => 5, 205 => 3]],
	],
	9 => [
		'required' => ['quest' => 8],
		'task' => ['planets' => 2],
		'reward' => ['build' => [605 => 259200]],
	],
	10 => [
		'required' => ['quest' => 9],
		'task' => ['fleet_mission' => Mission::Recycling],
		'reward' => ['build' => [209 => 3]],
	]
];
