<?php

namespace App\Models\Observers;

use App\Models\Alliance;
use App\Models\Statistic;
use App\Models\User;

class AllianceObserver
{
	public function deleted(Alliance $model): void
	{
		$this->performDelete($model);
	}

	public function trashed(Alliance $model): void
	{
		$this->performDelete($model);
	}

	protected function performDelete(Alliance $model): void
	{
		User::query()
			->whereBelongsTo($model)
			->update(['alliance_id' => null, 'alliance_name' => null]);

		Statistic::query()
			->whereBelongsTo($model)
			->where('stat_type', 1)
			->where('user_id', null)
			->delete();
	}
}
