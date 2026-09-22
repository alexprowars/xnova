<?php

return [
	'log_decisions' => env('AI_LOG_DECISIONS', false),
	'saving_horizon_hours' => 3,
	'max_batch' => 20,
	'shipyard_hours' => 2,
	'search_radius' => 100,
	'search_galaxy_radius' => 2,
	'target_limit' => 30,
	'active_humans_threshold' => 10,
	'active_humans_hours' => 24,
	'bot_target_bonus' => 3,
	'report_lifetime_minutes' => 120,
	'scout_cooldown_minutes' => 20,
	'attack_cooldown_minutes' => 180,
	'max_probes' => 32,
	'max_flight_hours' => 12,
	'max_colonization_flight_hours' => 48,
	'max_bots_per_system' => 3,
	'min_raid_profit' => 1000,
	'max_loss_ratio' => 0.15,
	'combat_margin' => 1.15,
];
