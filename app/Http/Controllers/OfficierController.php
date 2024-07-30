<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\Vars;
use App\Exceptions\Exception;
use App\Models\LogCredit;
use Illuminate\Http\Request;

class OfficierController extends Controller
{
	public function index()
	{
		$parse['items'] = [];

		foreach (Vars::getItemsByType(ItemType::OFFICIER) as $officier) {
			$parse['items'][] = [
				'id' => $officier,
				'power' => __('officier.power.' . $officier),
			];
		}

		return response()->state($parse);
	}

	public function buy(Request $request)
	{
		$id = (int) $request->post('id');
		$duration = (int) $request->post('duration');

		if (!$id || !$duration) {
			throw new Exception('Ошибка входных параметров');
		}

		$credits = match ($duration) {
			7 => 20,
			14 => 40,
			30 => 80,
			default => throw new Exception('Ошибка входных параметров'),
		};

		$time = $duration * 86400;

		if (!$time || $this->user->credits < $credits) {
			throw new Exception(__('officier.NoPoints'));
		}

		if (Vars::getItemType($id) != ItemType::OFFICIER) {
			throw new Exception('Выбран неверный элемент');
		}

		if ($this->user->{Vars::getName($id)}?->isFuture()) {
			$this->user->{Vars::getName($id)} = $this->user->{Vars::getName($id)}->addSeconds($time);
		} else {
			$this->user->{Vars::getName($id)} = now()->addSeconds($time);
		}

		$this->user->credits -= $credits;
		$this->user->update();

		LogCredit::create([
			'user_id' => $this->user->id,
			'amount' => $credits * (-1),
			'type' => 5
		]);
	}
}
