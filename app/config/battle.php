<?php

define('BATTLE_WIN', 1);
define('BATTLE_LOSE', -1);
define('BATTLE_DRAW', 0);
define('SHIELD_CELLS', 20000); //how many cells a ship's shield should contain. Carefull to edit: more cells = better accuracy but less bounces in some cases.
define('USE_BIEXPLOSION_SYSTEM', true); // enable below system value
define('PROB_TO_REAL_MAGIC', 2); //value used to adapt probability theory to critical cases.
define('EPSILON', 1.2e-6);

define('ROUNDS', 6);
define('SHIELDS_TECH_INCREMENT_FACTOR', 1); //how much a level increase the shield, in percentage from 0 to 1.
define('ARMOUR_TECH_INCREMENT_FACTOR', 1); //how much a level increase the armour, in percentage from 0 to 1.
define('WEAPONS_TECH_INCREMENT_FACTOR', 1); //how much a level increase the weapon, in percentage from 0 to 1.
define('COST_TO_ARMOUR', 0.1); //how much cost equal the armour, from 0 to 1. 1 means the ships/defenses armour equal its cost.
define('MIN_PROB_TO_EXPLODE', 0.3); //minimum probability at one the ships/defenses can explode, from 0 to 1. 1 means that the ship/def can explode only when they lost all hp.
define('DEFENSE_REPAIR_PROB', 0.7);
define('SHIP_REPAIR_PROB', 0); //same as below but for ships.
define('USE_HITSHIP_LIMITATION', false); //this option will limit the number of exploding ships to the number of total shots received by all defender's ships.
define('USE_EXPLODED_LIMITATION',true); //if true the number of exploded ships each round are limited to the damaged ships amount.
define('USE_RF', true); // enable rapid fire
define('USE_RANDOMIC_RF', true); // enable below system values
define('MAX_RF_BUFF', 0.1); // how much the rapid fire can be randomically increased.
define('MAX_RF_NERF', 0.1); // how much the rapid fire can be randomically decreased.

define('ONLY_FIRST_AND_LAST_ROUND', false);

define('REPAIRED_DO_DEBRIS',true);
define('SHIP_DEBRIS_FACTOR', 0.3);
define('DEFENSE_DEBRIS_FACTOR', 0);
define('POINT_UNIT', 1000);
define('MOON_UNIT_PROB', 100000);
define('MAX_MOON_PROB', 20);
define('MOON_MIN_START_SIZE', 2000);
define('MOON_MAX_START_SIZE', 6000);
define('MOON_MIN_FACTOR', 100);
define('MOON_MAX_FACTOR', 200);
define('MOON_MAX_HIGHT_TEMP_DIFFERENCE_FROM_PLANET', 30);
define('MOON_MAX_LOW_TEMP_DIFFERENCE_FROM_PLANET', 10);
define('DEFAULT_MOON_NAME', '');

?>