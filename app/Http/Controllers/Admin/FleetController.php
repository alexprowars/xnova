<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use App\Models\Fleet;
use App\Services\FleetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FleetController extends AdminController
{
	public function index(Request $request)
	{
		Access::authorize('fleets');

		return $this->listing(
			$request,
			'fleets',
			Fleet::query()->with('user'),
			['id', 'mission', 'ships', 'user', 'origin', 'destination', 'resources', 'arrival'],
			[Presentation::field('id'), Presentation::field('user_id')],
			function (Fleet $fleet) {
				return [
					'id' => $fleet->id,
					'can_return' => $fleet->canBack(),
					'mission' => $fleet->mission->title(),
					'user' => Presentation::userLink($fleet->user),
					'origin' => $fleet->splitStartPosition() . ' · ' . $fleet->start_type->title(),
					'destination' => $fleet->splitTargetPosition() . ' · ' . $fleet->end_type->title(),
					'ships' => [
						'text' => $fleet->entities->sum('count'),
						'tooltip' => $fleet->entities
							->map(fn($entity) => __('main.tech.' . $entity->id) . ': ' . $entity->count)
							->implode("\n"),
					],
					'resources' =>
						$fleet->resource_metal . ' / ' . $fleet->resource_crystal . ' / ' . $fleet->resource_deuterium,
					'arrival' => Presentation::value(
						match ($fleet->mess) {
							1 => $fleet->end_date,
							3 => $fleet->end_stay,
							default => $fleet->start_date,
						},
					),
				];
			},
			false,
		);
	}

	public function action(Request $request, Fleet $fleet)
	{
		Access::authorize('fleets');

		$data = $request->validate([
			'action' => ['required', Rule::in(['delete', 'accelerate', 'return'])],
		]);

		DB::transaction(function () use ($fleet, $data) {
			$fleet->refreshForUpdate();

			if ($data['action'] === 'delete') {
				$fleet->delete();
				return;
			}

			if ($data['action'] === 'return') {
				FleetService::recall($fleet);
				return;
			}

			$now = now()->addSeconds(5);

			if ($fleet->mess == 1) {
				$fleet->end_date = $now;
			} elseif ($fleet->mess == 3) {
				$returnDuration = max(0, $fleet->end_date->timestamp - $fleet->end_stay->timestamp);

				$fleet->end_stay = $now;
				$fleet->end_date = $now->addSeconds($returnDuration);
			} else {
				$shift = $now->timestamp - $fleet->start_date->timestamp;

				$fleet->start_date = $now;
				$fleet->end_date = $fleet->end_date->addSeconds($shift);

				if ($fleet->end_stay) {
					$fleet->end_stay = $fleet->end_stay->addSeconds($shift);
				}
			}

			$fleet->save();
		});

		return back()->with('admin_notice', __('admin.saved'));
	}
}
