<?php

namespace App\Models;

use App\Engine\Enums\PlanetType;
use Illuminate\Database\Eloquent\Model;

class Assault extends Model
{
	public $timestamps = false;
	protected $table = 'assaults';

	protected function casts(): array
	{
		return [
			'planet_type' => PlanetType::class,
		];
	}

	public function users()
	{
		return $this->hasMany(AssaultUser::class, 'assault_id');
	}

	public function fleets()
	{
		return $this->hasMany(Fleet::class, 'assault_id');
	}
}
