<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget as BaseWidget;

class VersionWidget extends BaseWidget
{
	protected static string $view = 'filament.widgets.version';
	protected static bool $isLazy = false;
}
