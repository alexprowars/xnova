<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VersionWidget extends BaseWidget
{
	protected function getStats(): array
	{
		return [
			Stat::make('Версия сервера', VERSION),
		];
	}
}
