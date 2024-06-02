<?php

namespace App\Http\Controllers;

use App\Engine\Vars;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Models\LogCredit;
use Illuminate\Http\Request;

class OfficierController extends Controller
{
	public function buy(Request $request)
	{
		if ($this->user->isVacation()) {
			throw new PageException('В режиме отпуска данный раздел недоступен!');
		}

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

		if (Vars::getItemType($id) != Vars::ITEM_TYPE_OFFICIER) {
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

		throw new RedirectException('/officier', __('officier.OffiRecrute'));
	}

	public function index()
	{
		$parse['credits'] = $this->user->credits;
		$parse['items'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $officier) {
			$row = [];
			$row['id'] = $officier;
			$row['time'] = null;

			if ($this->user->{Vars::getName($officier)}?->isFuture()) {
				$row['time'] = $this->user->{Vars::getName($officier)}->utc()->toAtomString();
			}

			$row['power'] = __('officier.power.' . $officier);

			$parse['items'][] = $row;
		}

		return response()->state($parse);
	}
}
