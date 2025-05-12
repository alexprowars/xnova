<?php

namespace App\Models;

use App\Engine\Enums\PlanetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assault extends Model
{
	public $timestamps = false;
	protected $table = 'assaults';
	protected $guarded = false;

	protected $casts = [
		'planet_type' => PlanetType::class,
	];

	/** @return HasMany<AssaultUser, $this> */
	public function users(): HasMany
	{
		return $this->hasMany(AssaultUser::class, 'assault_id');
	}

	/** @return HasMany<Fleet, $this> */
	public function fleets(): HasMany
	{
		return $this->hasMany(Fleet::class, 'assault_id');
	}
}
