<?php

namespace Xnova\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Xnova\Entity\Coordinates;

/**
 * @property $id
 * @property $image
 * @property $name
 * @property $id_owner
 * @property $id_ally
 * @property $planet_type
 * @property $field_current
 * @property $field_max
 * @property $last_update
 * @property $metal
 * @property $crystal
 * @property $deuterium
 * @property $energy_ak
 * @property $temp_min
 * @property $temp_max
 * @property $queue
 * @property $last_active
 * @property $debris_metal
 * @property $debris_crystal
 * @property $galaxy
 * @property $planet
 * @property $system
 * @property $diameter
 * @property $parent_planet
 * @property $last_jump_time
 * @property $destruyed
 */
class Planet extends Model
{
	use CrudTrait;

	public $timestamps = false;
	protected $hidden = ['planet_updated'];
	protected $guarded = [];

	public static function findByCoordinates(Coordinates $target): ?self
	{
		return self::query()->where('galaxy', $target->getGalaxy())
			->where('system', $target->getSystem())
			->where('planet', $target->getPosition())
			->where('planet_type', $target->getType() ?: Coordinates::TYPE_PLANET)
			->first();
	}
}
