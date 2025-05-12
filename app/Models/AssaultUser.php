<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssaultUser extends Model
{
	public $timestamps = false;
	protected $table = 'assaults_users';
	protected $guarded = false;

	/** @return BelongsTo<Assault, $this> */
	public function assault(): BelongsTo
	{
		return $this->belongsTo(Assault::class);
	}

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
