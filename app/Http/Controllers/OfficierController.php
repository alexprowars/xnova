<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Http\Request;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Controller;
use Xnova\Models\LogCredit;
use Xnova\Vars;

class OfficierController extends Controller
{
	protected $loadPlanet = true;

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

		switch ($duration) {
			case 7:
				$credits = 20;
				break;

			case 14:
				$credits = 40;
				break;

			case 30:
				$credits = 80;
				break;

			default:
				throw new ErrorException('Ошибка входных параметров');
		}

		$time = $duration * 86400;

		if (!$credits || !$time || $this->user->credits < $credits) {
			throw new ErrorException(__('officier.NoPoints'));
		}

		if (Vars::getItemType($id) != Vars::ITEM_TYPE_OFFICIER) {
			throw new ErrorException('Выбран неверный элемент');
		}

		if ($this->user->{Vars::getName($id)} > time()) {
			$this->user->{Vars::getName($id)} = $this->user->{Vars::getName($id)} + $time;
		} else {
			$this->user->{Vars::getName($id)} = time() + $time;
		}

		$this->user->credits -= $credits;
		$this->user->update();

		LogCredit::query()->insert([
			'uid' => $this->user->id,
			'time' => time(),
			'credits' => $credits * (-1),
			'type' => 5
		]);

		throw new RedirectException(__('officier.OffiRecrute'), '/officier/');
	}

	public function index()
	{
		$parse['credits'] = (int) $this->user->credits;
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

		$this->setTitle('Офицеры');
		$this->showTopPanel(false);

		return $parse;
	}
}
