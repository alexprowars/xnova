<?php

namespace App\Console\Commands;

use App\Jobs\FleetMissionJob;
use App\Models;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class Fleet extends Command
{
	protected $signature = 'game:fleet';

	public function handle(): void
	{
		$fleetIds = Models\Fleet::query()
			->where(function (Builder $query) {
				$query->whereNowOrPast('start_date')
					->where('mess', 0);
			})
			->orWhere(function (Builder $query) {
				$query->whereNotNull('end_stay')
					->whereNowOrPast('end_stay')
					->where('mess', 3);
			})
			->orWhere(function (Builder $query) {
				$query->whereNowOrPast('end_date')
					->whereNot('mess', 0);
			})
			->orderBy('updated_at')
			->pluck('id');

		foreach ($fleetIds as $fleetId) {
			dispatch(new FleetMissionJob($fleetId));
		}
	}
}
