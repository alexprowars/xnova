<?

/**
 * Игровые массивы
 * @author AlexPro
 * @copyright 2008 - 2013 XNova Game Group
 * @global $reslist array Массив типов построек
 * @global $resource array Массив символьных обозначений построек
 * @global $requeriments array Массив необходимых для построек требований
 * @global $pricelist array Массив стоимости построек
 * @global $gun_armour array Массив типов брони
 * @global $CombatCaps array Массив боевых параметров кораблей
 * @global $ProdGrid array Массив производства ресурсов постройками
 * ICQ: 8696096, Skype: alexprowars, Email: alexprowars@gmail.com
 */

if (!defined('INSIDE'))
	die();

$resource = array
(
	1 => "metal_mine",
	2 => "crystal_mine",
	3 => "deuterium_mine",
	4 => "solar_plant",
	12 => "fusion_plant",
	14 => "robot_factory",
	15 => "nano_factory",
	21 => "hangar",
	22 => "metal_store",
	23 => "crystal_store",
	24 => "deuterium_store",
	31 => "laboratory",
	33 => "terraformer",
	34 => "ally_deposit",
	41 => "mondbasis",
	42 => "phalanx",
	43 => "sprungtor",
	44 => "silo",

	106 => "spy_tech",
	108 => "computer_tech",
	109 => "military_tech",
	110 => "shield_tech",
	111 => "defence_tech",
	113 => "energy_tech",
	114 => "hyperspace_tech",
	115 => "combustion_tech",
	117 => "impulse_motor_tech",
	118 => "hyperspace_motor_tech",
	120 => "laser_tech",
	121 => "ionic_tech",
	122 => "buster_tech",
	123 => "intergalactic_tech",
	124 => "expedition_tech",
	150 => "colonisation_tech",
	161 => "fleet_base_tech",
	199 => "graviton_tech",

	202 => "small_ship_cargo",
	203 => "big_ship_cargo",
	204 => "light_hunter",
	205 => "heavy_hunter",
	206 => "crusher",
	207 => "battle_ship",
	208 => "colonizer",
	209 => "recycler",
	210 => "spy_sonde",
	211 => "bomber_ship",
	212 => "solar_satelit",
	213 => "destructor",
	214 => "dearth_star",
	215 => "battle_cruiser",
	216 => "fly_base",

	220 => "corvete",
	221 => "interceptor",
	222 => "dreadnought",
	223 => "corsair",

	302 => "fleet_202",
	303 => "fleet_203",
	304 => "fleet_204",
	305 => "fleet_205",
	306 => "fleet_206",
	307 => "fleet_207",
	309 => "fleet_209",
	311 => "fleet_211",
	313 => "fleet_213",
	314 => "fleet_214",
	315 => "fleet_215",

	320 => "fleet_220",
	321 => "fleet_221",
	322 => "fleet_222",
	323 => "fleet_223",

	351 => "fleet_401",
	352 => "fleet_402",
	353 => "fleet_403",
	354 => "fleet_404",
	355 => "fleet_405",
	356 => "fleet_406",

	401 => "misil_launcher",
	402 => "small_laser",
	403 => "big_laser",
	404 => "gauss_canyon",
	405 => "ionic_canyon",
	406 => "buster_canyon",
	407 => "small_protection_shield",
	408 => "big_protection_shield",

	502 => "interceptor_misil",
	503 => "interplanetary_misil",

	601 => "rpg_geologue",
	602 => "rpg_admiral",
	603 => "rpg_ingenieur",
	604 => "rpg_technocrate",
	605 => "rpg_constructeur",
	606 => "rpg_meta",
	607 => "rpg_komandir",

	700 => "race"
);

$requeriments = array
(
	6 => array(4 => 6, 113 => 6),

	12 => array(3 => 5, 113 => 3),
	15 => array(14 => 10, 108 => 10),
	21 => array(14 => 2),
	33 => array(15 => 1, 113 => 12),

	42 => array(41 => 1),
	43 => array(41 => 1, 114 => 7),

	106 => array(31 => 3),
	108 => array(31 => 1),
	109 => array(31 => 4),
	110 => array(113 => 3, 31 => 6),
	111 => array(31 => 2),
	113 => array(31 => 1),
	114 => array(113 => 5, 110 => 5, 31 => 7),
	115 => array(113 => 1, 31 => 1),
	117 => array(113 => 1, 31 => 2),
	118 => array(114 => 3, 31 => 7),
	120 => array(31 => 1, 113 => 2),
	121 => array(31 => 4, 120 => 5, 113 => 4),
	122 => array(31 => 5, 113 => 8, 120 => 10, 121 => 5),
	123 => array(31 => 10, 108 => 8, 114 => 8),
	124 => array(31 => 3, 108 => 4, 117 => 3),
	150 => array(31 => 3, 113 => 5, 111 => 2),
	161 => array(31 => 11),
	199 => array(31 => 12),

	202 => array(21 => 2, 115 => 2),
	203 => array(21 => 4, 115 => 6),
	204 => array(21 => 1, 115 => 1),
	205 => array(21 => 3, 111 => 2, 117 => 2),
	206 => array(21 => 5, 117 => 4, 121 => 2),
	207 => array(21 => 7, 118 => 4),
	208 => array(21 => 4, 117 => 3, 150 => 1),
	209 => array(21 => 4, 115 => 6, 110 => 2),
	210 => array(21 => 3, 115 => 3, 106 => 2),
	211 => array(117 => 6, 21 => 8, 122 => 5),
	212 => array(21 => 1),
	213 => array(21 => 9, 118 => 6, 114 => 5),
	214 => array(21 => 12, 199 => 1, 118 => 7, 114 => 6),
	215 => array(21 => 8, 114 => 5, 120 => 12, 118 => 5),
	216 => array(21 => 8, 161 => 1, 118 => 6),

	220 => array(21 => 5, 117 => 4, 109 => 4, 113 => 4, 700 => 1),
	221 => array(21 => 4, 117 => 3, 111 => 5, 700 => 2),
	222 => array(21 => 8, 114 => 4, 118 => 5, 700 => 3),
	223 => array(21 => 5, 111 => 5, 117 => 3, 700 => 4),

	302 => array(21 => 2, 115 => 2),
	303 => array(21 => 4, 115 => 6),
	304 => array(21 => 1, 115 => 1),
	305 => array(21 => 3, 111 => 2, 117 => 2),
	306 => array(21 => 5, 117 => 4, 121 => 2),
	307 => array(21 => 7, 118 => 4),
	309 => array(21 => 4, 115 => 6, 110 => 2),
	311 => array(117 => 6, 21 => 8, 122 => 5),
	313 => array(21 => 9, 118 => 6, 114 => 5),
	314 => array(21 => 12, 118 => 7, 114 => 6, 199 => 1),
	315 => array(21 => 8, 114 => 5, 120 => 12, 118 => 5),

	320 => array(21 => 5, 117 => 4, 109 => 4, 113 => 4, 700 => 1),
	321 => array(21 => 4, 117 => 3, 111 => 5, 700 => 2),
	322 => array(21 => 8, 114 => 4, 118 => 5, 700 => 3),
	323 => array(21 => 5, 111 => 5, 117 => 3, 700 => 4),

	351 => array(21 => 1),
	352 => array(113 => 1, 21 => 2, 120 => 3),
	353 => array(113 => 3, 21 => 4, 120 => 6),
	354 => array(21 => 6, 113 => 6, 109 => 3, 110 => 1),

	401 => array(21 => 1),
	402 => array(113 => 1, 21 => 2, 120 => 3),
	403 => array(113 => 3, 21 => 4, 120 => 6),
	404 => array(21 => 6, 113 => 6, 109 => 3, 110 => 1),
	405 => array(21 => 4, 121 => 4),
	406 => array(21 => 8, 122 => 7),
	407 => array(110 => 2, 21 => 1),
	408 => array(110 => 6, 21 => 6),
	502 => array(44 => 2, 21 => 1),
	503 => array(44 => 4, 21 => 1, 117 => 1),
);

$pricelist = array
(
	1 => array('metal' => 60, 'crystal' => 15, 'deuterium' => 0, 'factor' => 3 / 2),
	2 => array('metal' => 48, 'crystal' => 24, 'deuterium' => 0, 'factor' => 1.6),
	3 => array('metal' => 225, 'crystal' => 75, 'deuterium' => 0, 'factor' => 3 / 2),
	4 => array('metal' => 75, 'crystal' => 30, 'deuterium' => 0, 'factor' => 3 / 2),
	6 => array('metal' => 20000, 'crystal' => 15000, 'deuterium' => 7500, 'factor' => 2),
	12 => array('metal' => 900, 'crystal' => 360, 'deuterium' => 180, 'factor' => 1.8),
	14 => array('metal' => 400, 'crystal' => 120, 'deuterium' => 200, 'factor' => 2),
	15 => array('metal' => 1000000, 'crystal' => 500000, 'deuterium' => 100000, 'factor' => 2),
	21 => array('metal' => 400, 'crystal' => 200, 'deuterium' => 100, 'factor' => 2),
	22 => array('metal' => 2000, 'crystal' => 0, 'deuterium' => 0, 'factor' => 2),
	23 => array('metal' => 2000, 'crystal' => 1000, 'deuterium' => 0, 'factor' => 2),
	24 => array('metal' => 2000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 2),
	25 => array('metal' => 1000, 'crystal' => 2000, 'deuterium' => 4000, 'factor' => 2),
	31 => array('metal' => 200, 'crystal' => 400, 'deuterium' => 200, 'factor' => 2),
	33 => array('metal' => 0, 'crystal' => 50000, 'deuterium' => 100000, 'energy_max' => 1000, 'factor' => 2),
	34 => array('metal' => 20000, 'crystal' => 40000, 'deuterium' => 0, 'factor' => 2),
	41 => array('metal' => 20000, 'crystal' => 40000, 'deuterium' => 20000, 'factor' => 2),
	42 => array('metal' => 20000, 'crystal' => 40000, 'deuterium' => 20000, 'factor' => 2),
	43 => array('metal' => 2000000, 'crystal' => 4000000, 'deuterium' => 2000000, 'factor' => 2),
	44 => array('metal' => 20000, 'crystal' => 20000, 'deuterium' => 1000, 'factor' => 2),

	106 => array('metal' => 200, 'crystal' => 1000, 'deuterium' => 200, 'factor' => 2),
	108 => array('metal' => 0, 'crystal' => 400, 'deuterium' => 600, 'factor' => 2),
	109 => array('metal' => 800, 'crystal' => 200, 'deuterium' => 0, 'factor' => 2),
	110 => array('metal' => 200, 'crystal' => 600, 'deuterium' => 0, 'factor' => 2),
	111 => array('metal' => 1000, 'crystal' => 0, 'deuterium' => 0, 'factor' => 2),
	113 => array('metal' => 0, 'crystal' => 800, 'deuterium' => 400, 'factor' => 2),
	114 => array('metal' => 0, 'crystal' => 4000, 'deuterium' => 2000, 'factor' => 2),
	115 => array('metal' => 400, 'crystal' => 0, 'deuterium' => 600, 'factor' => 2),
	117 => array('metal' => 2000, 'crystal' => 4000, 'deuterium' => 6000, 'factor' => 2),
	118 => array('metal' => 10000, 'crystal' => 20000, 'deuterium' => 6000, 'factor' => 2),
	120 => array('metal' => 200, 'crystal' => 100, 'deuterium' => 0, 'factor' => 2),
	121 => array('metal' => 1000, 'crystal' => 300, 'deuterium' => 100, 'factor' => 2),
	122 => array('metal' => 2000, 'crystal' => 4000, 'deuterium' => 1000, 'factor' => 2),
	123 => array('metal' => 240000, 'crystal' => 400000, 'deuterium' => 160000, 'factor' => 2, 'max' => 8),
	124 => array('metal' => 4000, 'crystal' => 8000, 'deuterium' => 4000, 'factor' => 2),
	150 => array('metal' => 2000, 'crystal' => 8000, 'deuterium' => 2000, 'factor' => 2, 'max' => 8),
	161 => array('metal' => 32000, 'crystal' => 64000, 'deuterium' => 64000, 'factor' => 2),
	199 => array('metal' => 0, 'crystal' => 0, 'deuterium' => 0, 'energy_max' => 300000, 'factor' => 3, 'max' => 1),

	202 => array('metal' => 2000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 1),
	203 => array('metal' => 6000, 'crystal' => 6000, 'deuterium' => 0, 'factor' => 1),
	204 => array('metal' => 3000, 'crystal' => 1000, 'deuterium' => 0, 'factor' => 1),
	205 => array('metal' => 6000, 'crystal' => 4000, 'deuterium' => 0, 'factor' => 1),
	206 => array('metal' => 20000, 'crystal' => 7000, 'deuterium' => 2000, 'factor' => 1),
	207 => array('metal' => 45000, 'crystal' => 15000, 'deuterium' => 0, 'factor' => 1),
	208 => array('metal' => 10000, 'crystal' => 20000, 'deuterium' => 10000, 'factor' => 1),
	209 => array('metal' => 10000, 'crystal' => 6000, 'deuterium' => 2000, 'factor' => 1),
	210 => array('metal' => 0, 'crystal' => 1000, 'deuterium' => 0, 'factor' => 1),
	211 => array('metal' => 50000, 'crystal' => 25000, 'deuterium' => 15000, 'factor' => 1),
	212 => array('metal' => 0, 'crystal' => 2000, 'deuterium' => 500, 'factor' => 1),
	213 => array('metal' => 60000, 'crystal' => 50000, 'deuterium' => 15000, 'factor' => 1),
	214 => array('metal' => 5000000, 'crystal' => 4000000, 'deuterium' => 1000000, 'factor' => 1),
	215 => array('metal' => 30000, 'crystal' => 40000, 'deuterium' => 15000, 'factor' => 1),
	216 => array('metal' => 60000, 'crystal' => 80000, 'deuterium' => 75000, 'factor' => 1),

	220 => array('metal' => 30000, 'crystal' => 10000, 'deuterium' => 2500, 'factor' => 1),
	221 => array('metal' => 13000, 'crystal' => 3500, 'deuterium' => 1000, 'factor' => 1),
	222 => array('metal' => 50000, 'crystal' => 30000, 'deuterium' => 5000, 'factor' => 1),
	223 => array('metal' => 8000, 'crystal' => 4000, 'deuterium' => 500, 'factor' => 1),

	302 => array('metal' => 1000, 'crystal' => 0, 'deuterium' => 0, 'factor' => 2, 'max' => 10),
	303 => array('metal' => 3000, 'crystal' => 1000, 'deuterium' => 0, 'factor' => 2, 'max' => 10),
	304 => array('metal' => 2000, 'crystal' => 400, 'deuterium' => 0, 'factor' => 2, 'max' => 10),
	305 => array('metal' => 3000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 2, 'max' => 10),
	306 => array('metal' => 10000, 'crystal' => 3500, 'deuterium' => 0, 'factor' => 2, 'max' => 10),
	307 => array('metal' => 20000, 'crystal' => 10000, 'deuterium' => 1000, 'factor' => 2, 'max' => 10),
	309 => array('metal' => 6000, 'crystal' => 4000, 'deuterium' => 1000, 'factor' => 2, 'max' => 10),
	311 => array('metal' => 25000, 'crystal' => 12500, 'deuterium' => 7500, 'factor' => 2, 'max' => 10),
	313 => array('metal' => 60000, 'crystal' => 50000, 'deuterium' => 15000, 'factor' => 2, 'max' => 10),
	314 => array('metal' => 500000, 'crystal' => 300000, 'deuterium' => 100000, 'factor' => 2, 'max' => 10),
	315 => array('metal' => 20000, 'crystal' => 10000, 'deuterium' => 5000, 'factor' => 2, 'max' => 10),

	320 => array('metal' => 10000, 'crystal' => 2000, 'deuterium' => 2000, 'factor' => 2, 'max' => 10),
	321 => array('metal' => 7500, 'crystal' => 2000, 'deuterium' => 500, 'factor' => 2, 'max' => 10),
	322 => array('metal' => 25000, 'crystal' => 15000, 'deuterium' => 2500, 'factor' => 2, 'max' => 10),
	323 => array('metal' => 4000, 'crystal' => 2500, 'deuterium' => 0, 'factor' => 2, 'max' => 10),

	351 => array('metal' => 4000, 'crystal' => 2000, 'deuterium' => 500, 'factor' => 2, 'max' => 10),
	352 => array('metal' => 3000, 'crystal' => 3000, 'deuterium' => 500, 'factor' => 2, 'max' => 10),
	353 => array('metal' => 15000, 'crystal' => 10000, 'deuterium' => 3000, 'factor' => 2, 'max' => 10),
	354 => array('metal' => 30000, 'crystal' => 20000, 'deuterium' => 6000, 'factor' => 2, 'max' => 10),

	401 => array('metal' => 2000, 'crystal' => 0, 'deuterium' => 0, 'factor' => 1),
	402 => array('metal' => 1500, 'crystal' => 500, 'deuterium' => 0, 'factor' => 1),
	403 => array('metal' => 6000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 1),
	404 => array('metal' => 20000, 'crystal' => 15000, 'deuterium' => 2000, 'factor' => 1),
	405 => array('metal' => 2000, 'crystal' => 6000, 'deuterium' => 0, 'factor' => 1),
	406 => array('metal' => 50000, 'crystal' => 50000, 'deuterium' => 30000, 'factor' => 1),
	407 => array('metal' => 10000, 'crystal' => 10000, 'deuterium' => 0, 'factor' => 1, 'max' => 1),
	408 => array('metal' => 30000, 'crystal' => 30000, 'deuterium' => 0, 'factor' => 1, 'max' => 1),

	502 => array('metal' => 8000, 'crystal' => 2000, 'deuterium' => 0, 'factor' => 1),
	503 => array('metal' => 12500, 'crystal' => 2500, 'deuterium' => 10000, 'factor' => 1),
);

$gun_armour = array
(
	0 => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0),
	// Легкая
	1 => array(0 => 0, 1 => 25, 2 => 100, 3 => 10, 4 => 100),
	// Средняя
	2 => array(0 => 0, 1 => 100, 2 => 35, 3 => 20, 4 => 15),
	// Тяжелая
	3 => array(0 => 0, 1 => 25, 2 => 30, 3 => 100, 4 => 20)
);

// Оружие
// 1 лазерное
// 2 ионное
// 3 плазменное

$CombatCaps = array
(
	// малый транспорт
	202 => array
	(
		'attack' => 5,
		'shield' => 10,
		'consumption' => 10,
		'speed' => 5000,
		'engine_up' => array('tech' => 117, 'lvl' => 5, 'engine' => 2, 'speed' => 10000),
		'type_engine' => 1,
		'capacity' => 5000,
		'stay' => 5,
		'type_gun' => 1,
		'type_armour' => 1,
		'power_up' => 0,
		'power_armour' => 3,
		'power_consumption' => 5,
		'sd' => array (210 => 5, 212 => 5)
	),
	// большой транспорт
	203 => array
	(
		'attack' => 5,
		'shield' => 25,
		'consumption' => 50,
		'speed' => 7500,
		'type_engine' => 1,
		'capacity' => 25000,
		'stay' => 5,
		'type_gun' => 1,
		'type_armour' => 2,
		'power_up' => 0,
		'power_armour' => 5,
		'power_consumption' => 5,
		'sd' => array (210 => 5, 212 => 5)
	),
	// легкий истребитель
	204 => array
	(
		'attack' => 50,
		'shield' => 10,
		'consumption' => 20,
		'speed' => 12500,
		'type_engine' => 1,
		'capacity' => 50,
		'stay' => 2,
		'type_gun' => 1,
		'type_armour' => 1,
		'power_up' => 5,
		'power_armour' => 8,
		'power_consumption' => 0,
		'sd' => array (210 => 5, 212 => 5)
	),
	// тяжелый истребитель
	205 => array(
		'attack' => 150,
		'shield' => 25,
		'consumption' => 75,
		'speed' => 10000,
		'type_engine' => 2,
		'capacity' => 100,
		'stay' => 7,
		'type_gun' => 3,
		'type_armour' => 2,
		'power_up' => 5,
		'power_armour' => 10,
		'power_consumption' => 0,
		'sd' => array (202 => 3, 210 => 5, 212 => 5)
	),
	// крейсер
	206 => array
	(
		'attack' => 400,
		'shield' => 50,
		'consumption' => 300,
		'speed' => 15000,
		'type_engine' => 2,
		'capacity' => 800,
		'stay' => 30,
		'type_gun' => 2,
		'type_armour' => 1,
		'power_up' => 8,
		'power_armour' => 12,
		'power_consumption' => 0,
		'sd' => array (204 => 6, 401 => 10, 210 => 5, 212 => 5)
	),
	// Линкор
	207 => array
	(
		'attack' => 1000,
		'shield' => 200,
		'consumption' => 500,
		'speed' => 10000,
		'type_engine' => 3,
		'capacity' => 1500,
		'stay' => 50,
		'type_gun' => 3,
		'type_armour' => 2,
		'power_up' => 8,
		'power_armour' => 15,
		'power_consumption' => 0,
		'sd' => array (210 => 5, 212 => 5)
	),
	// Колонизатор
	208 => array
	(
		'attack' => 50,
		'shield' => 100,
		'consumption' => 1000,
		'speed' => 2500,
		'type_engine' => 2,
		'capacity' => 7500,
		'stay' => 100,
		'type_gun' => 1,
		'type_armour' => 4,
		'power_up' => 0,
		'power_armour' => 10,
		'power_consumption' => 0,
		'sd' => array (210 => 5, 212 => 5)
	),
	// Переработчик
	209 => array
	(
		'attack' => 1,
		'shield' => 10,
		'consumption' => 300,
		'speed' => 2000,
		'type_engine' => 1,
		'capacity' => 20000,
		'stay' => 30,
		'type_gun' => 1,
		'type_armour' => 3,
		'power_up' => 0,
		'power_armour' => 3,
		'power_consumption' => 5,
		'sd' => array (210 => 5, 212 => 5)
	),
	// Шпионский зонд
	210 => array
	(
		'attack' => 1,
		'shield' => 0,
		'consumption' => 1,
		'speed' => 100000000,
		'type_engine' => 1,
		'capacity' => 5,
		'stay' => 0.1,
		'type_gun' => 1,
		'type_armour' => 1,
		'power_up' => 0,
		'power_armour' => 5,
		'power_consumption' => 0,
		'sd' => array ()
	),
	// Бомбардировщик
	211 => array
	(
		'attack' => 1000,
		'shield' => 500,
		'consumption' => 1000,
		'speed' => 4000,
		'engine_up' => array('tech' => 118, 'lvl' => 8, 'engine' => 3, 'speed' => 5000),
		'type_engine' => 2,
		'capacity' => 500,
		'stay' => 100,
		'type_gun' => 1,
		'type_armour' => 3,
		'power_up' => 11,
		'power_armour' => 16,
		'power_consumption' => 0,
		'sd' => array (210 => 5, 212 => 5, 401 =>  20, 402 =>  20, 403 =>  10, 405 =>  10)
	),
	// Солнечный спутник
	212 => array
	(
		'attack' => 1,
		'shield' => 1,
		'consumption' => 0,
		'speed' => 0,
		'type_engine' => 0,
		'capacity' => 0,
		'stay' => 0,
		'type_gun' => 1,
		'type_armour' => 3,
		'power_up' => 0,
		'power_armour' => 1,
		'power_consumption' => 0,
		'sd' => array ()
	),
	// Уничтожитель
	213 => array
	(
		'attack' => 2000,
		'shield' => 500,
		'consumption' => 1000,
		'speed' => 5000,
		'type_engine' => 3,
		'capacity' => 2000,
		'stay' => 100,
		'type_gun' => 3,
		'type_armour' => 3,
		'power_up' => 11,
		'power_armour' => 18,
		'power_consumption' => 0,
		'sd' => array (210 => 5, 212 => 5, 215 => 2, 221 => 3, 402 =>  10)
	),
	// Звезда смерти
	214 => array
	(
		'attack' => 200000,
		'shield' => 50000,
		'consumption' => 1,
		'speed' => 100,
		'type_engine' => 3,
		'capacity' => 1000000,
		'stay' => 0.1,
		'type_gun' => 2,
		'type_armour' => 4,
		'power_up' => 15,
		'power_armour' => 25,
		'power_consumption' => 0,
		'sd' => array (210 => 1250, 212 => 1250, 202 => 250, 203 => 250, 208 => 250, 209 => 250, 204 => 200, 205 => 100, 206 => 33, 207 => 30, 211 => 25, 215 => 15, 213 => 5, 220 => 10, 221 => 10, 222 => 10, 223 => 10, 401 => 200, 402 => 200, 403 => 100, 404 =>  50, 405 => 100)
	),
	// Линейный крейсер
	215 => array
	(
		'attack' => 700,
		'shield' => 400,
		'consumption' => 250,
		'speed' => 10000,
		'type_engine' => 3,
		'capacity' => 750,
		'stay' => 25,
		'type_gun' => 1,
		'type_armour' => 2,
		'power_up' => 8,
		'power_armour' => 13,
		'power_consumption' => 0,
		'sd' => array (202 => 3, 203 => 3, 205 => 4, 206 => 4, 207 => 10, 210 => 5, 212 => 5, 223 => 3)
	),

	// Передвижная база
	216 => array
	(
		'attack' => 10,
		'shield' => 10,
		'consumption' => 40,
		'speed' => 4500,
		'type_engine' => 3,
		'capacity' => 20000,
		'stay' => 30,
		'type_gun' => 1,
		'type_armour' => 1,
		'power_up' => 0,
		'power_armour' => 1,
		'power_consumption' => 0,
		'sd' => array()
	),

	// Корвет
	220 => array
	(
		'attack' => 500,
		'shield' => 300,
		'consumption' => 250,
		'speed' => 12500,
		'type_engine' => 2,
		'capacity' => 800,
		'stay' => 30,
		'type_gun' => 2,
		'type_armour' => 2,
		'power_up' => 8,
		'power_armour' => 11,
		'power_consumption' => 0,
		'sd' => array(210 => 5, 212 => 5, 2104 => 5, 205 => 4, 206 => 2)
	),
	// Перехватчик
	221 => array
	(
		'attack' => 300,
		'shield' => 100,
		'consumption' => 330,
		'speed' => 17000,
		'type_engine' => 2,
		'capacity' => 600,
		'stay' => 30,
		'type_gun' => 1,
		'type_armour' => 1,
		'power_up' => 7,
		'power_armour' => 11,
		'power_consumption' => 0,
		'sd' => array(204 => 2, 210 => 5, 212 => 5)
	),
	// Дредноут
	222 => array
	(
		'attack' => 1250,
		'shield' => 200,
		'consumption' => 700,
		'speed' => 10000,
		'type_engine' => 3,
		'capacity' => 2100,
		'stay' => 30,
		'type_gun' => 2,
		'type_armour' => 2,
		'power_up' => 8,
		'power_armour' => 16,
		'power_consumption' => 0,
		'sd' => array(210 => 5, 212 => 5, 206 => 2, 207 => 5, 401 => 2, 402 => 2)
	),
	// Корсар
	223 => array
	(
		'attack' => 200,
		'shield' => 50,
		'consumption' => 50,
		'speed' => 10000,
		'type_engine' => 2,
		'capacity' => 600,
		'stay' => 30,
		'type_gun' => 3,
		'type_armour' => 2,
		'power_up' => 5,
		'power_armour' => 11,
		'power_consumption' => 0,
		'sd' => array(210 => 5, 212 => 5, 401 => 4, 402 => 3)
	),

	401 => array
	(
		'attack' => 80,
		'shield' => 20,
		'type_gun' => 1,
		'type_armour' => 4,
		'power_up' => 4,
		'power_armour' => 4,
		'sd' => array()
	),
	402 => array
	(
		'attack' => 100,
		'shield' => 25,
		'type_gun' => 1,
		'type_armour' => 4,
		'power_up' => 4,
		'power_armour' => 4,
		'sd' => array()
	),
	403 => array
	(
		'attack' => 250,
		'shield' => 100,
		'type_gun' => 1,
		'type_armour' => 4,
		'power_up' => 3,
		'power_armour' => 3,
		'sd' => array()
	),
	404 => array
	(
		'attack' => 1100,
		'shield' => 200,
		'type_gun' => 3,
		'type_armour' => 4,
		'power_up' => 3,
		'power_armour' => 3,
		'sd' => array()
	),
	405 => array
	(
		'attack' => 150,
		'shield' => 500,
		'type_gun' => 2,
		'type_armour' => 4,
		'power_up' => 0,
		'power_armour' => 0,
		'sd' => array()
	),
	406 => array
	(
		'attack' => 3000,
		'shield' => 300,
		'type_gun' => 3,
		'type_armour' => 4,
		'power_up' => 0,
		'power_armour' => 0,
		'sd' => array()
	),

	407 => array('attack' => 1, 'shield' => 1000, 'type_gun' => 0, 'type_armour' => 0, 'power_up' => 0, 'power_armour' => 0, 'sd' => array()),
	408 => array('attack' => 1, 'shield' => 10000, 'type_gun' => 0, 'type_armour' => 0, 'power_up' => 0, 'power_armour' => 0, 'sd' => array()),

	502 => array('attack' => 1, 'power_up' => 0, 'power_armour' => 0),
	503 => array('attack' => 12000, 'power_up' => 0, 'power_armour' => 0)
);

$ProdGrid = array(
	1 => array(
		'metal' => 'return (30 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);',
		'crystal' => 'return 0;',
		'deuterium' => 'return 0;',
		'energy' => 'return -(10 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'
	),
	2 => array(
		'metal' => 'return 0;',
		'crystal' => 'return (20 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);',
		'deuterium' => 'return 0;',
		'energy' => 'return -(10 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'
	),
	3 => array(
		'metal' => 'return 0;',
		'crystal' => 'return 0;',
		'deuterium' => 'return ((10 * $BuildLevel * pow((1.1), $BuildLevel)) * (-0.002 * $BuildTemp + 1.28)) * (0.1 * $BuildLevelFactor);',
		'energy' => 'return -(30 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'
	),
	4 => array(
		'metal' => 'return 0;',
		'crystal' => 'return 0;',
		'deuterium' => 'return 0;',
		'energy' => 'return (20 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor) * (1 + $energyTech * 0.01);'
	),
	12 => array(
		'metal' => 'return 0;',
		'crystal' => 'return 0;',
		'deuterium' => 'return -(10 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);',
		'energy' => 'return (40 * $BuildLevel * pow(1.05 + $energyTech * 0.01, $BuildLevel)) * (0.1 * $BuildLevelFactor) ;'
	),
	212 => array(
		'metal' => 'return 0;',
		'crystal' => 'return 0;',
		'deuterium' => 'return 0;',
		'energy' => 'return floor(($BuildTemp + 160) / 6) * $BuildLevel * (0.1 * $BuildLevelFactor);'
	)
);

$reslist['build'] = array(1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 44, 41, 42, 43);
$reslist['tech'] = array(106, 108, 109, 110, 111, 113, 114, 115, 117, 118, 120, 121, 122, 123, 124, 150, 161, 199);
$reslist['tech_f'] = array(302, 303, 304, 305, 306, 307, 309, 311, 313, 314, 315, 320, 321, 322, 323, 351, 352, 353, 354);
$reslist['fleet'] = array(202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 220, 221, 222, 223);
$reslist['defense'] = array(401, 402, 403, 404, 405, 406, 407, 408, 502, 503);
$reslist['officier'] = array(601, 602, 603, 604, 605, 606, 607);
$reslist['prod'] = array(1, 2, 3, 4, 12, 212);
$reslist['res'] = array('metal', 'crystal', 'deuterium');

$reslist['allowed'][1] = array(1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 44);
$reslist['allowed'][3] = array(14, 21, 34, 41, 42, 43);
$reslist['allowed'][5] = array(14, 34, 43, 44);

?>