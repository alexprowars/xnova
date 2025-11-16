<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LogIp extends Model
{
	use MassPrunable;

	protected $table = 'log_ips';
	protected $guarded = [];

	public function prunable()
	{
		return static::query()->where('created_at', '<', now()->subDays(7));
	}
}
