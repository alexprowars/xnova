<?php

namespace App\Http\Controllers;

use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models\LogsCredit;
use Illuminate\Http\Request;

class OfficiersController extends Controller
{
	public function index(): array
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

		return $items;
	}

	public function buy(Request $request): void
	{
		$code = $request->post('code');
		$duration = (int) $request->post('duration', 0);

		if (!$code || !$duration) {
			throw new Exception('Ошибка входных параметров');
		}

		$credits = match ($duration) {
			7 => 20,
			14 => 40,
			30 => 80,
			default => throw new Exception('Ошибка входных параметров'),
		};

		$time = $duration * 86400;

		if ($this->user->credits < $credits) {
			throw new Exception(__('officier.NoPoints'));
		}

		if (!in_array($code, Vars::getOfficiers())) {
			throw new Exception('Выбран неверный элемент');
		}

		if ($this->user->{'officier_' . $code}?->isFuture()) {
			$date = $this->user->{'officier_' . $code};
		} else {
			$date = now();
		}

		$this->user->{'officier_' . $code} = $date->addSeconds($time);
		$this->user->credits -= $credits;
		$this->user->update();

		LogsCredit::create([
			'user_id' => $this->user->id,
			'amount' => $credits * (-1),
			'type' => 5
		]);
	}
}
