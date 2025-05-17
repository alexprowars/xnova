<?php

/**
 * @global $reslist array Массив типов построек
 * @global $resource array Массив символьных обозначений построек
 * @global $requeriments array Массив необходимых для построек требований
 * @global $pricelist array Массив стоимости построек
 * @global $gun_armour array Массив типов брони
 * @global $CombatCaps array Массив боевых параметров кораблей
 * @global $ProdGrid array Массив производства ресурсов постройками
 */

use App\Models\Planet;

$resource = [
	1 => 'metal_mine',
	2 => 'crystal_mine',
	3 => 'deuterium_mine',
	4 => 'solar_plant',
	12 => 'fusion_plant',
	14 => 'robot_factory',
	15 => 'nano_factory',
	21 => 'hangar',
	22 => 'metal_store',
	23 => 'crystal_store',
	24 => 'deuterium_store',
	31 => 'laboratory',
	33 => 'terraformer',
	34 => 'ally_deposit',
	41 => 'moonbase',
	42 => 'phalanx',
	43 => 'jumpgate',
	44 => 'missile_facility',

	106 => 'spy_tech',
	108 => 'computer_tech',
	109 => 'military_tech',
	110 => 'shield_tech',
	111 => 'defence_tech',
	113 => 'energy_tech',
	114 => 'hyperspace_tech',
	115 => 'combustion_tech',
	117 => 'impulse_motor_tech',
	118 => 'hyperspace_motor_tech',
	120 => 'laser_tech',
	121 => 'ionic_tech',
	122 => 'buster_tech',
	123 => 'intergalactic_tech',
	124 => 'expedition_tech',
	150 => 'colonization_tech',
	161 => 'fleet_base_tech',
	199 => 'graviton_tech',

	202 => 'small_ship_cargo',
	203 => 'big_ship_cargo',
	204 => 'light_hunter',
	205 => 'heavy_hunter',
	206 => 'crusher',
	207 => 'battle_ship',
	208 => 'colonizer',
	209 => 'recycler',
	210 => 'spy_sonde',
	211 => 'bomber_ship',
	212 => 'solar_satelit',
	213 => 'destructor',
	214 => 'dearth_star',
	215 => 'battle_cruiser',
	216 => 'fly_base',

	220 => 'corvete',
	221 => 'interceptor',
	222 => 'dreadnought',
	223 => 'corsair',

	401 => 'misil_launcher',
	402 => 'small_laser',
	403 => 'big_laser',
	404 => 'gauss_canyon',
	405 => 'ionic_canyon',
	406 => 'buster_canyon',
	407 => 'small_protection_shield',
	408 => 'big_protection_shield',

	502 => 'interceptor_misil',
	503 => 'interplanetary_misil',

	601 => 'rpg_geologue',
	602 => 'rpg_admiral',
	603 => 'rpg_ingenieur',
	604 => 'rpg_technocrate',
	605 => 'rpg_constructeur',
	606 => 'rpg_meta',
	607 => 'rpg_komandir',

	700 => 'race'
];

$requeriments = [
	6 => [4 => 6, 113 => 6],

	12 => [3 => 5, 113 => 3],
	15 => [14 => 10, 108 => 10],
	21 => [14 => 2],
	33 => [15 => 1, 113 => 12],

	42 => [41 => 1],
	43 => [41 => 1, 114 => 7],

	106 => [31 => 3],
	108 => [31 => 1],
	109 => [31 => 4],
	110 => [31 => 6, 113 => 3],
	111 => [31 => 2],
	113 => [31 => 1],
	114 => [31 => 7, 110 => 5, 113 => 5],
	115 => [31 => 1, 113 => 1],
	117 => [113 => 1, 31 => 2],
	118 => [114 => 3, 31 => 7],
	120 => [31 => 1, 113 => 2],
	121 => [31 => 4, 113 => 4, 120 => 5],
	122 => [31 => 5, 113 => 8, 120 => 10, 121 => 5],
	123 => [31 => 10, 108 => 8, 114 => 8],
	124 => [31 => 3, 108 => 4, 117 => 3],
	150 => [31 => 3, 111 => 2, 113 => 5],
	161 => [31 => 11],
	199 => [31 => 12],

	202 => [21 => 2, 115 => 2],
	203 => [21 => 4, 115 => 6],
	204 => [21 => 1, 115 => 1],
	205 => [21 => 3, 111 => 2, 117 => 2],
	206 => [21 => 5, 117 => 4, 121 => 2],
	207 => [21 => 7, 118 => 4],
	208 => [21 => 4, 117 => 3, 150 => 1],
	209 => [21 => 4, 110 => 2, 115 => 6],
	210 => [21 => 3, 106 => 2, 115 => 3],
	211 => [21 => 8, 117 => 6, 122 => 5],
	212 => [21 => 1],
	213 => [21 => 9, 114 => 5, 118 => 6],
	214 => [21 => 12, 114 => 6, 118 => 7, 199 => 1],
	215 => [21 => 8, 114 => 5, 118 => 5, 120 => 12],
	216 => [21 => 8, 118 => 6, 161 => 1],

	220 => [21 => 5, 109 => 4, 113 => 4, 117 => 4, 700 => 1],
	221 => [21 => 4, 111 => 5, 117 => 3, 700 => 2],
	222 => [21 => 8, 114 => 4, 118 => 5, 700 => 3],
	223 => [21 => 5, 111 => 5, 117 => 3, 700 => 4],

	401 => [21 => 1],
	402 => [21 => 2, 113 => 1, 120 => 3],
	403 => [21 => 4, 113 => 3, 120 => 6],
	404 => [21 => 6, 109 => 3, 110 => 1, 113 => 6],
	405 => [21 => 4, 121 => 4],
	406 => [21 => 8, 122 => 7],
	407 => [110 => 2, 21 => 1],
	408 => [110 => 6, 21 => 6],
	502 => [21 => 1, 44 => 2],
	503 => [21 => 1, 44 => 4, 117 => 1],
];

$pricelist = [
	1 => ['metal' => 60, 'crystal' => 15, 'deuterium' => 0, 'factor' => 3 / 2],
	2 => ['metal' => 48, 'crystal' => 24, 'deuterium' => 0, 'factor' => 1.6],
	3 => ['metal' => 225, 'crystal' => 75, 'deuterium' => 0, 'factor' => 3 / 2],
	4 => ['metal' => 75, 'crystal' => 30, 'deuterium' => 0, 'factor' => 3 / 2],
	6 => ['metal' => 20000, 'crystal' => 15000, 'deuterium' => 7500, 'factor' => 2],
	12 => ['metal' => 900, 'crystal' => 360, 'deuterium' => 180, 'factor' => 1.8],
	14 => ['metal' => 400, 'crystal' => 120, 'deuterium' => 200, 'factor' => 2],
	15 => ['metal' => 1000000, 'crystal' => 500000, 'deuterium' => 100000, 'factor' => 2],
	21 => ['metal' => 400, 'crystal' => 200, 'deuterium' => 100, 'factor' => 2],
	22 => ['metal' => 2000, 'crystal' => 0, 'deuterium' => 0, 'factor' => 2],
	23 => ['metal' => 2000, 'crystal' => 1000, 'deuterium' => 0, 'factor' => 2],
	24 => ['metal' => 2000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 2],
	25 => ['metal' => 1000, 'crystal' => 2000, 'deuterium' => 4000, 'factor' => 2],
	31 => ['metal' => 200, 'crystal' => 400, 'deuterium' => 200, 'factor' => 2],
	33 => ['metal' => 0, 'crystal' => 50000, 'deuterium' => 100000, 'energy' => 1000, 'factor' => 2],
	34 => ['metal' => 20000, 'crystal' => 40000, 'deuterium' => 0, 'factor' => 2],
	41 => ['metal' => 20000, 'crystal' => 40000, 'deuterium' => 20000, 'factor' => 2],
	42 => ['metal' => 20000, 'crystal' => 40000, 'deuterium' => 20000, 'factor' => 2],
	43 => ['metal' => 2000000, 'crystal' => 4000000, 'deuterium' => 2000000, 'factor' => 2],
	44 => ['metal' => 20000, 'crystal' => 20000, 'deuterium' => 1000, 'factor' => 2],

	106 => ['metal' => 200, 'crystal' => 1000, 'deuterium' => 200, 'factor' => 2],
	108 => ['metal' => 0, 'crystal' => 400, 'deuterium' => 600, 'factor' => 2],
	109 => ['metal' => 800, 'crystal' => 200, 'deuterium' => 0, 'factor' => 2],
	110 => ['metal' => 200, 'crystal' => 600, 'deuterium' => 0, 'factor' => 2],
	111 => ['metal' => 1000, 'crystal' => 0, 'deuterium' => 0, 'factor' => 2],
	113 => ['metal' => 0, 'crystal' => 800, 'deuterium' => 400, 'factor' => 2],
	114 => ['metal' => 0, 'crystal' => 4000, 'deuterium' => 2000, 'factor' => 2],
	115 => ['metal' => 400, 'crystal' => 0, 'deuterium' => 600, 'factor' => 2],
	117 => ['metal' => 2000, 'crystal' => 4000, 'deuterium' => 6000, 'factor' => 2],
	118 => ['metal' => 10000, 'crystal' => 20000, 'deuterium' => 6000, 'factor' => 2],
	120 => ['metal' => 200, 'crystal' => 100, 'deuterium' => 0, 'factor' => 2],
	121 => ['metal' => 1000, 'crystal' => 300, 'deuterium' => 100, 'factor' => 2],
	122 => ['metal' => 2000, 'crystal' => 4000, 'deuterium' => 1000, 'factor' => 2],
	123 => ['metal' => 240000, 'crystal' => 400000, 'deuterium' => 160000, 'factor' => 2, 'max' => 8],
	124 => ['metal' => 4000, 'crystal' => 8000, 'deuterium' => 4000, 'factor' => 2],
	150 => ['metal' => 2000, 'crystal' => 8000, 'deuterium' => 2000, 'factor' => 2, 'max' => 8],
	161 => ['metal' => 32000, 'crystal' => 64000, 'deuterium' => 64000, 'factor' => 2],
	199 => ['metal' => 0, 'crystal' => 0, 'deuterium' => 0, 'energy' => 300000, 'factor' => 3, 'max' => 1],

	202 => ['metal' => 2000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 1],
	203 => ['metal' => 6000, 'crystal' => 6000, 'deuterium' => 0, 'factor' => 1],
	204 => ['metal' => 3000, 'crystal' => 1000, 'deuterium' => 0, 'factor' => 1],
	205 => ['metal' => 6000, 'crystal' => 4000, 'deuterium' => 0, 'factor' => 1],
	206 => ['metal' => 20000, 'crystal' => 7000, 'deuterium' => 2000, 'factor' => 1],
	207 => ['metal' => 45000, 'crystal' => 15000, 'deuterium' => 0, 'factor' => 1],
	208 => ['metal' => 10000, 'crystal' => 20000, 'deuterium' => 10000, 'factor' => 1],
	209 => ['metal' => 10000, 'crystal' => 6000, 'deuterium' => 2000, 'factor' => 1],
	210 => ['metal' => 0, 'crystal' => 1000, 'deuterium' => 0, 'factor' => 1],
	211 => ['metal' => 50000, 'crystal' => 25000, 'deuterium' => 15000, 'factor' => 1],
	212 => ['metal' => 0, 'crystal' => 2000, 'deuterium' => 500, 'factor' => 1],
	213 => ['metal' => 60000, 'crystal' => 50000, 'deuterium' => 15000, 'factor' => 1],
	214 => ['metal' => 5000000, 'crystal' => 4000000, 'deuterium' => 1000000, 'factor' => 1],
	215 => ['metal' => 30000, 'crystal' => 40000, 'deuterium' => 15000, 'factor' => 1],
	216 => ['metal' => 60000, 'crystal' => 80000, 'deuterium' => 75000, 'factor' => 1],

	220 => ['metal' => 30000, 'crystal' => 10000, 'deuterium' => 2500, 'factor' => 1],
	221 => ['metal' => 13000, 'crystal' => 3500, 'deuterium' => 1000, 'factor' => 1],
	222 => ['metal' => 50000, 'crystal' => 30000, 'deuterium' => 5000, 'factor' => 1],
	223 => ['metal' => 8000, 'crystal' => 4000, 'deuterium' => 500, 'factor' => 1],

	401 => ['metal' => 2000, 'crystal' => 0, 'deuterium' => 0, 'factor' => 1],
	402 => ['metal' => 1500, 'crystal' => 500, 'deuterium' => 0, 'factor' => 1],
	403 => ['metal' => 6000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 1],
	404 => ['metal' => 20000, 'crystal' => 15000, 'deuterium' => 2000, 'factor' => 1],
	405 => ['metal' => 2000, 'crystal' => 6000, 'deuterium' => 0, 'factor' => 1],
	406 => ['metal' => 50000, 'crystal' => 50000, 'deuterium' => 30000, 'factor' => 1],
	407 => ['metal' => 10000, 'crystal' => 10000, 'deuterium' => 0, 'factor' => 1, 'max' => 1],
	408 => ['metal' => 30000, 'crystal' => 30000, 'deuterium' => 0, 'factor' => 1, 'max' => 1],

	502 => ['metal' => 8000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 1],
	503 => ['metal' => 12500, 'crystal' => 2500, 'deuterium' => 10000, 'factor' => 1],
];

$gun_armour = [
	0 => [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0],
	// Легкая
	1 => [0 => 0, 1 => 25, 2 => 100, 3 => 10, 4 => 100],
	// Средняя
	2 => [0 => 0, 1 => 100, 2 => 35, 3 => 20, 4 => 15],
	// Тяжелая
	3 => [0 => 0, 1 => 25, 2 => 30, 3 => 100, 4 => 20]
];

// Оружие
// 1 лазерное
// 2 ионное
// 3 плазменное

$CombatCaps = [
	// малый транспорт
	202 => [
		'attack' => 5,
		'shield' => 10,
		'consumption' => 10,
		'speed' => 5000,
		'engine_up' => ['tech' => 117, 'lvl' => 5, 'engine' => 2, 'speed' => 10000],
		'type_engine' => 1,
		'capacity' => 5000,
		'stay' => 5,
		'type_gun' => 1,
		'type_armour' => 1,
		'sd' => [210 => 5, 212 => 5]
	],
	// большой транспорт
	203 => [
		'attack' => 5,
		'shield' => 25,
		'consumption' => 50,
		'speed' => 7500,
		'type_engine' => 1,
		'capacity' => 25000,
		'stay' => 5,
		'type_gun' => 1,
		'type_armour' => 2,
		'sd' => [210 => 5, 212 => 5]
	],
	// легкий истребитель
	204 => [
		'attack' => 50,
		'shield' => 10,
		'consumption' => 20,
		'speed' => 12500,
		'type_engine' => 1,
		'capacity' => 50,
		'stay' => 2,
		'type_gun' => 1,
		'type_armour' => 1,
		'sd' => [210 => 5, 212 => 5]
	],
	// тяжелый истребитель
	205 => [
		'attack' => 150,
		'shield' => 25,
		'consumption' => 75,
		'speed' => 10000,
		'type_engine' => 2,
		'capacity' => 100,
		'stay' => 7,
		'type_gun' => 3,
		'type_armour' => 2,
		'sd' => [202 => 3, 210 => 5, 212 => 5]
	],
	// крейсер
	206 => [
		'attack' => 400,
		'shield' => 50,
		'consumption' => 300,
		'speed' => 15000,
		'type_engine' => 2,
		'capacity' => 800,
		'stay' => 30,
		'type_gun' => 2,
		'type_armour' => 1,
		'sd' => [204 => 6, 210 => 5, 212 => 5, 401 => 10]
	],
	// Линкор
	207 => [
		'attack' => 1000,
		'shield' => 200,
		'consumption' => 500,
		'speed' => 10000,
		'type_engine' => 3,
		'capacity' => 1500,
		'stay' => 50,
		'type_gun' => 3,
		'type_armour' => 2,
		'sd' => [210 => 5, 212 => 5]
	],
	// Колонизатор
	208 => [
		'attack' => 50,
		'shield' => 100,
		'consumption' => 1000,
		'speed' => 2500,
		'type_engine' => 2,
		'capacity' => 7500,
		'stay' => 100,
		'type_gun' => 1,
		'type_armour' => 4,
		'sd' => [210 => 5, 212 => 5]
	],
	// Переработчик
	209 => [
		'attack' => 1,
		'shield' => 10,
		'consumption' => 300,
		'speed' => 2000,
		'type_engine' => 1,
		'capacity' => 20000,
		'stay' => 30,
		'type_gun' => 1,
		'type_armour' => 3,
		'sd' => [210 => 5, 212 => 5]
	],
	// Шпионский зонд
	210 => [
		'attack' => 1,
		'shield' => 0,
		'consumption' => 1,
		'speed' => 100000000,
		'type_engine' => 1,
		'capacity' => 5,
		'stay' => 0.1,
		'type_gun' => 1,
		'type_armour' => 1,
		'sd' => []
	],
	// Бомбардировщик
	211 => [
		'attack' => 1000,
		'shield' => 500,
		'consumption' => 1000,
		'speed' => 4000,
		'engine_up' => ['tech' => 118, 'lvl' => 8, 'engine' => 3, 'speed' => 5000],
		'type_engine' => 2,
		'capacity' => 500,
		'stay' => 100,
		'type_gun' => 1,
		'type_armour' => 3,
		'sd' => [210 => 5, 212 => 5, 401 =>  20, 402 =>  20, 403 =>  10, 405 =>  10]
	],
	// Солнечный спутник
	212 => [
		'attack' => 1,
		'shield' => 1,
		'consumption' => 0,
		'speed' => 0,
		'type_engine' => 0,
		'capacity' => 0,
		'stay' => 0,
		'type_gun' => 1,
		'type_armour' => 3,
		'sd' => []
	],
	// Уничтожитель
	213 => [
		'attack' => 2000,
		'shield' => 500,
		'consumption' => 1000,
		'speed' => 5000,
		'type_engine' => 3,
		'capacity' => 2000,
		'stay' => 100,
		'type_gun' => 3,
		'type_armour' => 3,
		'sd' => [210 => 5, 212 => 5, 215 => 2, 221 => 3, 402 =>  10]
	],
	// Звезда смерти
	214 => [
		'attack' => 200000,
		'shield' => 50000,
		'consumption' => 1,
		'speed' => 100,
		'type_engine' => 3,
		'capacity' => 1000000,
		'stay' => 0.1,
		'type_gun' => 2,
		'type_armour' => 4,
		'sd' => [202 => 250, 203 => 250, 204 => 200, 205 => 100, 206 => 33, 207 => 30, 208 => 250, 209 => 250, 210 => 1250, 211 => 25, 212 => 1250, 213 => 5, 215 => 15, 220 => 10, 221 => 10, 222 => 10, 223 => 10, 401 => 200, 402 => 200, 403 => 100, 404 =>  50, 405 => 100]
	],
	// Линейный крейсер
	215 => [
		'attack' => 700,
		'shield' => 400,
		'consumption' => 250,
		'speed' => 10000,
		'type_engine' => 3,
		'capacity' => 750,
		'stay' => 25,
		'type_gun' => 1,
		'type_armour' => 2,
		'sd' => [202 => 3, 203 => 3, 205 => 4, 206 => 4, 207 => 10, 210 => 5, 212 => 5, 223 => 3]
	],
	// Передвижная база
	216 => [
		'attack' => 10,
		'shield' => 10,
		'consumption' => 40,
		'speed' => 4500,
		'type_engine' => 3,
		'capacity' => 20000,
		'stay' => 30,
		'type_gun' => 1,
		'type_armour' => 1,
		'sd' => []
	],
	// Корвет
	220 => [
		'attack' => 500,
		'shield' => 300,
		'consumption' => 250,
		'speed' => 12500,
		'type_engine' => 2,
		'capacity' => 800,
		'stay' => 30,
		'type_gun' => 2,
		'type_armour' => 2,
		'sd' => [204 => 5, 205 => 4, 206 => 2, 210 => 5, 212 => 5]
	],
	// Перехватчик
	221 => [
		'attack' => 300,
		'shield' => 100,
		'consumption' => 330,
		'speed' => 17000,
		'type_engine' => 2,
		'capacity' => 600,
		'stay' => 30,
		'type_gun' => 1,
		'type_armour' => 1,
		'sd' => [204 => 2, 210 => 5, 212 => 5]
	],
	// Дредноут
	222 => [
		'attack' => 1250,
		'shield' => 200,
		'consumption' => 700,
		'speed' => 10000,
		'type_engine' => 3,
		'capacity' => 2100,
		'stay' => 30,
		'type_gun' => 2,
		'type_armour' => 2,
		'sd' => [206 => 2, 207 => 5, 210 => 5, 212 => 5, 401 => 2, 402 => 2]
	],
	// Корсар
	223 => [
		'attack' => 200,
		'shield' => 50,
		'consumption' => 50,
		'speed' => 10000,
		'type_engine' => 2,
		'capacity' => 600,
		'stay' => 30,
		'type_gun' => 3,
		'type_armour' => 2,
		'sd' => [210 => 5, 212 => 5, 401 => 4, 402 => 3]
	],
	401 => [
		'attack' => 80,
		'shield' => 20,
		'type_gun' => 1,
		'type_armour' => 4,
		'sd' => []
	],
	402 => [
		'attack' => 100,
		'shield' => 25,
		'type_gun' => 1,
		'type_armour' => 4,
		'sd' => []
	],
	403 => [
		'attack' => 250,
		'shield' => 100,
		'type_gun' => 1,
		'type_armour' => 4,
		'sd' => []
	],
	404 => [
		'attack' => 1100,
		'shield' => 200,
		'type_gun' => 3,
		'type_armour' => 4,
		'sd' => []
	],
	405 => [
		'attack' => 150,
		'shield' => 500,
		'type_gun' => 2,
		'type_armour' => 4,
		'sd' => []
	],
	406 => [
		'attack' => 3000,
		'shield' => 300,
		'type_gun' => 3,
		'type_armour' => 4,
		'sd' => []
	],
	407 => [
		'attack' => 1,
		'shield' => 1000,
		'type_gun' => 0,
		'type_armour' => 0,
		'sd' => []
	],
	408 => [
		'attack' => 1,
		'shield' => 10000,
		'type_gun' => 0,
		'type_armour' => 0,
		'sd' => []
	],
	502 => [
		'attack' => 1,
		'shield' => 0,
		'type_gun' => 0,
		'type_armour' => 0,
		'sd' => []
	],
	503 => [
		'attack' => 12000,
		'shield' => 0,
		'type_gun' => 0,
		'type_armour' => 0,
		'sd' => []
	]
];

$ProdGrid = [
	1 => [
		'metal' => fn($level, $factor) => 30 * $level * (1.1 ** $level) * (0.1 * $factor),
		'energy' => fn($level, $factor) => -floor(10 * $level * (1.1 ** $level) * (0.1 * $factor)),
	],
	2 => [
		'crystal' => fn($level, $factor) => 20 * $level * (1.1 ** $level) * (0.1 * $factor),
		'energy' => fn($level, $factor) => -floor(10 * $level * (1.1 ** $level) * (0.1 * $factor)),
	],
	3 => [
		'deuterium' => fn($level, $factor, Planet $planet) => (10 * $level * (1.1 ** $level) * (-0.004 * $planet->temp_max + 1.44)) * (0.1 * $factor),
		'energy' => fn($level, $factor) => -floor(20 * $level * (1.1 ** $level) * (0.1 * $factor)),
	],
	4 => [
		'energy' => fn($level, $factor, Planet $planet) => 20 * $level * (1.1 ** $level) * (0.1 * $factor),
	],
	12 => [
		'deuterium' => fn($level, $factor) => -floor(10 * $level * (1.1 ** $level) * (0.1 * $factor)),
		'energy' => fn($level, $factor, Planet $planet) => 30 * $level * ((1.05 + $planet->user->getTechLevel('energy') * 0.01) ** $level) * (0.1 * $factor),
	],
	212 => [
		'energy' => fn($level, $factor, Planet $planet) => floor(($planet->temp_max + 140) / 6) * $level * (0.1 * $factor),
	]
];

$reslist['build'] = [1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 44, 41, 42, 43];
$reslist['tech'] = [106, 108, 109, 110, 111, 113, 114, 115, 117, 118, 120, 121, 122, 123, 124, 150, 161, 199];
$reslist['fleet'] = [202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 220, 221, 222, 223];
$reslist['defense'] = [401, 402, 403, 404, 405, 406, 407, 408, 502, 503];
$reslist['officier'] = [601, 602, 603, 604, 605, 606, 607];
$reslist['prod'] = [1, 2, 3, 4, 12, 212];
$reslist['res'] = ['metal', 'crystal', 'deuterium'];

$reslist['allowed'][1] = [1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 44];
$reslist['allowed'][3] = [14, 21, 34, 41, 42, 43];
$reslist['allowed'][5] = [14, 34, 43, 44];

$reslist['build_exp'] = [1, 2, 3, 5, 22, 23, 24, 25];
