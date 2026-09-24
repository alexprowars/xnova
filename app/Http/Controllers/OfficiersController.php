<?php

namespace App\Http\Controllers;

use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Services\OfficierService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OfficiersController extends Controller
{
	public function index()
	{
		$items = [];

		foreach (Vars::getOfficiers() as $code) {
			$items[] = [
				'code' => $code,
				'name' => __('officier.items.' . $code),
				'description' => __('officier.description.' . $code),
				'power' => __('officier.power.' . $code),
			];
		}

		$contracts = [];

		foreach ([7 => 'cost_week', 14 => 'cost_weeks', 30 => 'cost_month'] as $days => $label) {
			$contracts[] = [
				'duration' => $days,
				'price' => OfficierService::getPrice($days),
				'label' => $label,
			];
		}

		return Inertia::render('Officiers', [
			'items' => $items,
			'contracts' => $contracts,
		]);
	}

	public function buy(Request $request): void
	{
		$code = $request->post('code');
		$duration = (int) $request->post('duration', 0);

		if (!is_string($code) || !$code || !$duration) {
			throw new Exception(__('officier.invalid_parameters'));
		}

		if (!OfficierService::buy($this->user, $code, $duration)) {
			throw new Exception(__('officier.no_points'));
		}
	}
}
