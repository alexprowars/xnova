<?php

namespace App\Models;

use App\Engine\EntityFactory;
use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
	public $timestamps = false;
	protected $guarded = [];

	protected $attributes = [
		'operation' => QueueConstructionType::BUILDING,
	];

	protected $casts = [
		'date' => 'immutable_datetime',
		'date_end' => 'immutable_datetime',
		'type' => QueueType::class,
		'operation' => QueueConstructionType::class,
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<Planet, $this> */
	public function planet(): BelongsTo
	{
		return $this->belongsTo(Planet::class, 'planet_id');
	}

	public function getTime(): int
	{
		if ($this->type === QueueType::BUILDING || $this->type === QueueType::RESEARCH) {
			$entity = EntityFactory::get(
				$this->object_id,
				$this->level - ($this->operation == QueueConstructionType::BUILDING ? 1 : 0),
				$this->planet
			);
		} else {
			$entity = EntityFactory::get(
				$this->object_id,
				1,
				$this->planet
			);
		}

		$result = $entity->getTime();

		if ($this->operation == QueueConstructionType::DESTROY) {
			$result = (int) ceil($result / 2);
		}

		return $result;
	}
}
