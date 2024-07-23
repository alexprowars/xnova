<?php

namespace App\Models;

use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
	public $timestamps = false;
	protected $guarded = false;

	protected $attributes = [
		'operation' => QueueConstructionType::BUILDING,
	];

	protected function casts(): array
	{
		return [
			'time' => 'immutable_datetime',
			'time_end' => 'immutable_datetime',
			'type' => QueueType::class,
			'operation' => QueueConstructionType::class,
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function planet()
	{
		return $this->belongsTo(Planet::class, 'planet_id');
	}
}
