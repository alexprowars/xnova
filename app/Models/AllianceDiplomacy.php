<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllianceDiplomacy extends Model
{
	public $timestamps = false;
	protected $table = 'alliances_diplomacies';

	/** @return BelongsTo<Alliance, $this> */
	public function alliance(): BelongsTo
	{
		return $this->belongsTo(Alliance::class);
	}

	/** @return BelongsTo<Alliance, $this> */
	public function diplomacy(): BelongsTo
	{
		return $this->belongsTo(Alliance::class);
	}
}
