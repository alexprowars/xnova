<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $planet_id
 * @property int|null $build_id
 * @property int $level
 * @property int $power
 */
class PlanetsBuildings extends Model
{
	public $timestamps = false;
	public $table = 'planets_buildings';
}
