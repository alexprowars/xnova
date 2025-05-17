<?php

namespace App\Events;

use App\Models\Fleet;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class FleetSended implements ShouldDispatchAfterCommit
{
	public function __construct(public Fleet $fleet)
	{
	}
}
