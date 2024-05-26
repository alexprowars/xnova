<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\ErrorException;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Controller;
use App\Models\LogCredit;
use App\Vars;

class OfficierController extends Controller
{
	public function buy(Request $request)
	{
		if ($this->user->vacation > 0) {
			throw new PageException('В режиме отпуска данный раздел недоступен!');
		}

		$id = (int) $request->post('id');
		$duration = (int) $request->post('duration');

		if (!$id || !$duration) {
			throw new ErrorException('Ошибка входных параметров');
		}

		$credits = match ($duration) {
			7 => 20,
			14 => 40,
			30 => 80,
			default => throw new ErrorException('Ошибка входных параметров'),
		};

		$time = $duration * 86400;

		if (!$time || $this->user->credits < $credits) {
			throw new ErrorException(__('officier.NoPoints'));
		}

		if (Vars::getItemType($id) != Vars::ITEM_TYPE_OFFICIER) {
			throw new ErrorException('Выбран неверный элемент');
		}

		if ($this->user->{Vars::getName($id)} > time()) {
			$this->user->{Vars::getName($id)} += $time;
		} else {
			$this->user->{Vars::getName($id)} = time() + $time;
		}

		$this->user->credits -= $credits;
		$this->user->update();

		LogCredit::create([
			'user_id' => $this->user->id,
			'amount' => $credits * (-1),
			'type' => 5
		]);

		throw new RedirectException(__('officier.OffiRecrute'), '/officier');
	}

	public function index()
	{
		$parse['credits'] = $this->user->credits;
		$parse['items'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $officier) {
			$row['id'] = $officier;
			$row['time'] = 0;

			if ($this->user->{Vars::getName($officier)} > time()) {
				$row['time'] = $this->user->{Vars::getName($officier)};
			}

			$row['description'] = __('officier.Desc.' . $officier);
			$row['power'] = __('officier.power.' . $officier);

			$parse['items'][] = $row;
		}

		return $parse;
	}
}
