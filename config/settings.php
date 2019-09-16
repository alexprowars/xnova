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
	'forum_url'               => 'http://forum.xnova.su/',
	// УРЛ форума
	'noobprotection'          => 1,
	// Защита новичков
	'noobprotectiontime'      => 50,
	'noobprotectionmulti'     => 5,
	'fleetDebrisRate'         => 0.3,
	// Флот в обломки
	'defsDebrisRate'          => 0,
	// Оборона в обломки
	'initial_fields'          => 170,
	// Поля на главной планете
	'initial_base_fields'     => 10,
	// Поля на военной базе
	'BuildLabWhileRun'        => 0,
	// Разрешить апгрейд лабы при идущем исследовании
	'vocationModeTime'        => 172800,
	// Время ухода в отпуск
	'metal_basic_income'      => 20,
	// Базовое производство на планете
	'crystal_basic_income'    => 10,
	'deuterium_basic_income'  => 0,
	'energy_basic_income'     => 0,
	'game_speed'              => 500000,
	// Скорость строительства и исследований /2500
	'fleet_speed'             => 500000,
	// Скорость полётов /2500
	'resource_multiplier'     => 30,
	// Скорость добычи ресурсов
	'planetFactor'            => 1,
	// Множитель размера колонизируемых планет
	'hallPoints'              => 1000000,
	// Порог лома для попадания в зал славы
	'maxMoonChance'           => 20,
	// Максимальный ШВЛ
	'refersCreditBonus'       => 5,
	// Ежедневный бонус за рефералов
	'maxRegPlanetsInSystem'   => 3,
	'buildings_exp_mult'      => 1500,
	'maxSlotsInSim'           => 10,
	'view' => [
		'openRaportInNewWindow' => 1,
		// Открывать отчетыв новом окне
		'showPlanetListSelect'  => 0,
		// Показывать селект выбора планет
		'socialIframeView'      => 0,
		// Вид для соц сетей
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
		'id'=> '',
		'login' => '',
		'password' => '',
		'from' => '',
	],
	'robokassa' => [
		'login' => 75835,
		'public' => 's9veqtsa',
		'secret' => 'ZYDX6A4ap9jfHdCqvTk9Pf7Wd6Z9jVF3',
	],
];