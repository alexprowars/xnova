<?php

return [
	'spyReportRow'            => 2,
	'fieldsByMoonBase'        => env('GAME_MOONBASE_FIELDS', 4),
	'maxPlanets'              => env('GAME_MAX_PLANETS', 9),
	'maxBuildingQueue'        => env('GAME_MAX_QUEUE', 1),
	'maxBuildingFleets'       => 99999,
	'maxGalaxyInWorld'        => env('GAME_MAX_GALAXY_IN_WORLD', 9),
	'maxSystemInGalaxy'       => env('GAME_MAX_SYSTEM_IN_WORLD', 499),
	'maxPlanetInSystem'       => env('GAME_MAX_PLANET_IN_WORLD', 15),
	'baseStorageSize'         => env('GAME_BASE_STORAGE_SIZE', 50000),
	'baseMetalProduction'     => env('GAME_PLANET_METAL', 5000),
	'baseCrystalProduction'   => env('GAME_PLANET_CRYSTAL', 5000),
	'baseDeuteriumProduction' => env('GAME_PLANET_DEUTERIUM', 5000),
	'onlinetime'              => 60,
	'universe'                => env('GAME_UNIVERSE', 'x'),
	// Защита новичков
	'noobprotection'          => env('GAME_NOOB_PROTECTION', 1),
	'noobprotectiontime'      => env('GAME_NOOB_PROTECTION_TIME', 50),
	'noobprotectionmulti'     => env('GAME_NOOB_PROTECTION_MULTI', 5),
	// Флот в обломки
	'fleetDebrisRate'         => env('GAME_FLEET_DEBRIS_RATE', 0.3),
	// Оборона в обломки
	'defsDebrisRate'          => env('GAME_DEFENSE_DEBRIS_RATE', 0),
	// Поля на главной планете
	'initial_fields'          => env('GAME_PLANET_FILEDS', 170),
	// Поля на военной базе
	'initial_base_fields'     => env('GAME_PLANET_BASE_FILEDS', 10),
	// Разрешить апгрейд лабы при идущем исследовании
	'BuildLabWhileRun'        => 0,
	// Время ухода в отпуск в днях
	'vacationModeTime'        => env('GAME_VACATION_MODE_DAYS', 2),
	// Базовое производство на планете
	'metal_basic_income'      => env('GAME_PLANET_METAL_PRODUCTION', 30),
	'crystal_basic_income'    => env('GAME_PLANET_CRYSTAL_PRODUCTION', 15),
	'deuterium_basic_income'  => env('GAME_PLANET_DEUTERIUM_PRODUCTION', 0),
	'energy_basic_income'     => env('GAME_PLANET_ENERGY_PRODUCTION', 0),
	// Скорость строительства и исследований
	'game_speed'              => env('GAME_BASE_SPEED', 1),
	// Скорость полётов
	'fleet_speed'             => env('GAME_FLEET_SPEED', 1),
	// Скорость добычи ресурсов
	'resource_multiplier'     => env('GAME_RESOURCE_SPEED', 1),
	// Множитель размера колонизируемых планет
	'planetFactor'            => env('GAME_PLANET_SIZE_FACTOR', 1),
	// Порог лома для попадания в зал славы
	'hallPoints'              => 1000000,
	// Максимальный ШВЛ
	'maxMoonChance'           => env('GAME_MAX_MOON_CHANCE', 20),
	// Ежедневный бонус за рефералов
	'refersCreditBonus'       => 5,
	'maxRegPlanetsInSystem'   => env('GAME_MAX_PLANET_IN_SYSTEM', 3),
	'buildings_exp_mult'      => 1500,
	'maxSlotsInSim'           => 10,
	'view' => [
		// Открывать отчетыв новом окне
		'openRaportInNewWindow' => 1,
		// Показывать селект выбора планет
		'showPlanetListSelect'  => 0,
	],
	'deleteTime'   => env('GAME_USER_DELETE_TIME', 7),
	'inactiveTime' => env('GAME_USER_INACTIVE_TIME', 21),
	'level' => [
		'max_ind' => 100,
		'max_war' => 100,
		'credits' => 10,
	],
	'log'   => [
		'research'  => env('GAME_LOG_RESEARCH', 1),
		'buildings' => env('GAME_LOG_BUILDINGS', 1),
		'factory'   => env('GAME_LOG_FACTORY', 1),
	],
];
