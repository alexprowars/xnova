<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Coordinates;

class Planet extends Model
{
	use CrudTrait;

	public $timestamps = false;
	protected $hidden = ['planet_updated'];
	protected $guarded = [];

	protected $attributes = [
		'metal' => 0,
		'crystal' => 0,
		'deuterium' => 0,
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public static function findByCoordinates(Coordinates $target): ?static
	{
		return self::query()->where('galaxy', $target->getGalaxy())
			->where('system', $target->getSystem())
			->where('planet', $target->getPlanet())
			->where('planet_type', $target->getType() ?: Coordinates::TYPE_PLANET)
			->first();
	}
}
