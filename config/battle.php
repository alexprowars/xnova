<?php

return [
	'SHIELD_CELLS' => 20000, //how many cells a ship's shield should contain. Carefull to edit: more cells = better accuracy but less bounces in some cases.
	'USE_BIEXPLOSION_SYSTEM' => true, // enable below system value
	'PROB_TO_REAL_MAGIC' => 2, //value used to adapt probability theory to critical cases.
	'EPSILON' => 1.2e-6,

	'ROUNDS' => 6,
	'SHIELDS_TECH_INCREMENT_FACTOR' => 1, //how much a level increase the shield, in percentage from 0 to 1.
	'ARMOUR_TECH_INCREMENT_FACTOR' => 1, //how much a level increase the armour, in percentage from 0 to 1.
	'WEAPONS_TECH_INCREMENT_FACTOR' => 1, //how much a level increase the weapon, in percentage from 0 to 1.
	'COST_TO_ARMOUR' => 0.1, //how much cost equal the armour, from 0 to 1. 1 means the ships/defenses armour equal its cost.
	'MIN_PROB_TO_EXPLODE' => 0.3, //minimum probability at one the ships/defenses can explode, from 0 to 1. 1 means that the ship/def can explode only when they lost all hp.
	'DEFENSE_REPAIR_PROB' => 0.7,
	'SHIP_REPAIR_PROB' => 0, //same as below but for ships.
	'USE_HITSHIP_LIMITATION' => false, //this option will limit the number of exploding ships to the number of total shots received by all defender's ships.
	'USE_EXPLODED_LIMITATION' => true, //if true the number of exploded ships each round are limited to the damaged ships amount.
	'USE_RF' => true, // enable rapid fire
	'USE_RANDOMIC_RF' => true, // enable below system values
	'MAX_RF_BUFF' => 0.1, // how much the rapid fire can be randomically increased.
	'MAX_RF_NERF' => 0.1, // how much the rapid fire can be randomically decreased.

	'ONLY_FIRST_AND_LAST_ROUND' => false,

	'REPAIRED_DO_DEBRIS' => true,
	'SHIP_DEBRIS_FACTOR' => 0.3,
	'DEFENSE_DEBRIS_FACTOR' => 0,
	'POINT_UNIT' => 1000,
	'MOON_UNIT_PROB' => 100000,
	'MAX_MOON_PROB' => 20,
	'MOON_MIN_START_SIZE' => 2000,
	'MOON_MAX_START_SIZE' => 6000,
	'MOON_MIN_FACTOR' => 100,
	'MOON_MAX_FACTOR' => 200,
	'MOON_MAX_HIGHT_TEMP_DIFFERENCE_FROM_PLANET' => 30,
	'MOON_MAX_LOW_TEMP_DIFFERENCE_FROM_PLANET' => 10,
	'DEFAULT_MOON_NAME' => '',
];
