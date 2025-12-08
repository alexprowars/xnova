<?php

namespace App\Console\Commands;

use App\Jobs\FleetMissionJob;
use App\Models;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class Fleet extends Command
{
	protected $signature = 'game:fleet';

	public function handle()
	{
		$fleets = Models\Fleet::query()
			->where(function (Builder $query) {
				$query->whereNowOrPast('start_date')
					->where('mess', 0);
			})
			->orWhere(function (Builder $query) {
				$query->whereNotNull('end_stay')
					->whereNowOrPast('end_stay')
					->whereNot('mess', 0);
			})
			->orWhere(function (Builder $query) {
				$query->whereNowOrPast('end_date')
					->whereNot('mess', 0);
			})
			->orderBy('updated_at')
			->get();

		foreach ($fleets as $fleet) {
			dispatch(new FleetMissionJob($fleet));
		}
	}
}
