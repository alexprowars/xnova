<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallOfFame extends Model
{
	public $timestamps = false;
	protected $table = 'halls_of_fame';
	protected $guarded = [];

	protected $casts = [
		'date' => 'immutable_datetime',
	];

	/** @return BelongsTo<LogsBattle, $this> */
	public function battleLog(): BelongsTo
	{
		return $this->belongsTo(LogsBattle::class, 'report_id');
	}
}
