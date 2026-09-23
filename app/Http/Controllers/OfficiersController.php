<?php

namespace App\Http\Controllers;

use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models\LogsCredit;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

		return Inertia::render('Officiers', [
			'items' => $items,
		]);
	}

	public function buy(Request $request): void
	{
		$code = $request->post('code');
		$duration = (int) $request->post('duration', 0);

		if (!$code || !$duration) {
			throw new Exception(__('officier.invalid_parameters'));
		}

		$credits = match ($duration) {
			7 => 20,
			14 => 40,
			30 => 80,
			default => throw new Exception(__('officier.invalid_parameters')),
		};

		$time = $duration * 86400;

		if (!in_array($code, Vars::getOfficiers())) {
			throw new Exception(__('officier.invalid_item'));
		}

		DB::transaction(function () use ($code, $credits, $time) {
			$this->user->refreshForUpdate();

			if ($this->user->credits < $credits) {
				throw new Exception(__('officier.no_points'));
			}

			$planets = $code === 'geologist'
				? $this->user->planets()->orderBy('id')->lockForUpdate()->get()
				: collect();

			$purchasedAt = CarbonImmutable::now();

			foreach ($planets as $planet) {
				$planet->setRelation('user', $this->user);
				$planet->getProduction($purchasedAt)->update();
			}

			if ($this->user->{'officier_' . $code}?->greaterThan($purchasedAt)) {
				$date = $this->user->{'officier_' . $code};
			} else {
				$date = $purchasedAt;
			}

			$this->user->{'officier_' . $code} = $date->addSeconds($time);
			$this->user->credits -= $credits;
			$this->user->update();

			LogsCredit::create([
				'user_id' => $this->user->id,
				'amount' => $credits * (-1),
				'type' => 5
			]);
		});
	}
}
