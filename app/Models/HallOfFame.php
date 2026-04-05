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

	/** @return BelongsTo<Report, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(Report::class, 'report_id');
	}
}
