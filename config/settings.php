<?php

return [
	'spyReportRow'            => 2,
	'fieldsByMoonBase'        => 4,
	'maxPlanets'              => 9,
	'maxBuildingQueue'        => 1,
	'maxBuildingFleets'       => 99999,
	'maxGalaxyInWorld'        => 4,
	'maxSystemInGalaxy'       => 499,
	'maxPlanetInSystem'       => 15,
	'baseStorageSize'         => 50000,
	'baseMetalProduction'     => 5000,
	'baseCristalProduction'   => 5000,
	'baseDeuteriumProduction' => 5000,
	'onlinetime'              => 60,
	'universe'                => 'x',
	// УРЛ форума
	'forum_url'               => 'http://forum.xnova.su/',
	// Защита новичков
	'noobprotection'          => 1,
	'noobprotectiontime'      => 50,
	'noobprotectionmulti'     => 5,
	// Флот в обломки
	'fleetDebrisRate'         => 0.3,
	// Оборона в обломки
	'defsDebrisRate'          => 0,
	// Поля на главной планете
	'initial_fields'          => 170,
	// Поля на военной базе
	'initial_base_fields'     => 10,
	// Разрешить апгрейд лабы при идущем исследовании
	'BuildLabWhileRun'        => 0,
	// Время ухода в отпуск
	'vocationModeTime'        => 172800,
	// Базовое производство на планете
	'metal_basic_income'      => 20,
	'crystal_basic_income'    => 10,
	'deuterium_basic_income'  => 0,
	'energy_basic_income'     => 0,
	// Скорость строительства и исследований /2500
	'game_speed'              => 500000,
	// Скорость полётов /2500
	'fleet_speed'             => 500000,
	// Скорость добычи ресурсов
	'resource_multiplier'     => 30,
	// Множитель размера колонизируемых планет
	'planetFactor'            => 1,
	// Порог лома для попадания в зал славы
	'hallPoints'              => 1000000,
	// Максимальный ШВЛ
	'maxMoonChance'           => 20,
	// Ежедневный бонус за рефералов
	'refersCreditBonus'       => 5,
	'maxRegPlanetsInSystem'   => 3,
	'buildings_exp_mult'      => 1500,
	'maxSlotsInSim'           => 10,
	'view' => [
		// Открывать отчетыв новом окне
		'openRaportInNewWindow' => 1,
		// Показывать селект выбора планет
		'showPlanetListSelect'  => 0,
	],
	'stat' => [
		'deleteTime'   => 604800,
		'inactiveTime' => 1814400,
	],
	'level' => [
		'max_ind' => 100,
		'max_war' => 100,
		'credits' => 10,
	],
	'log'   => [
		'research'  => 1,
		'buildings' => 1,
		'factory'   => 1,
	],
	'sms' => [
		'id' => '',
		'login' => '',
		'password' => '',
		'from' => '',
	],
	'recaptcha' => [
		'public_key' => env('RECAPTCHA_PUBLIC_KEY', ''),
		'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
	],
	'robokassa' => [
		'login' => 75835,
		'public' => 's9veqtsa',
		'secret' => 'ZYDX6A4ap9jfHdCqvTk9Pf7Wd6Z9jVF3',
	],
];
