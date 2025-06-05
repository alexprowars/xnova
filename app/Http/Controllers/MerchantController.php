<?php

namespace App\Http\Controllers;

use App\Engine\Game;
use App\Facades\Vars;
use App\Exceptions\Exception;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
	public function exchange(Request $request)
	{
		if ($this->user->credits <= 0) {
			throw new Exception('Недостаточно кредитов для проведения обменной операции');
		}

		$metal = (int) $request->post('metal', 0);
		$crystal = (int) $request->post('crystal', 0);
		$deuterium = (int) $request->post('deuterium', 0);

		if ($metal < 0 || $crystal < 0 || $deuterium < 0) {
			throw new Exception('Злобный читер');
		}

		$type = trim($request->post('type'));

		if (!in_array($type, Vars::getResources())) {
			throw new Exception('Ресурс не существует');
		}

		$exchangeRate = Game::getMerchantExchangeRate();

		$exchange = 0;

		foreach (Vars::getResources() as $res) {
			if ($res != $type) {
				$exchange += $$res * ($exchangeRate[$res] / $exchangeRate[$type]);
			}
		}

		if ($exchange <= 0) {
			throw new Exception('Вы не можете обменять такое количество ресурсов');
		}

		if ($this->planet->{$type} < $exchange) {
			throw new Exception('На планете недостаточно ресурсов данного типа');
		}

		$this->planet->{$type} -= $exchange;

		foreach (Vars::getResources() as $res) {
			if ($res != $type) {
				$this->planet->{$res} += $$res;
			}
		}

		$this->planet->update();

		$this->user->credits -= 1;
		$this->user->update();

		$quest = $this->user->quests()
			->where('quest_id', 6)
			->where('finish', false)
			->where('stage', 0)
			->first();

		if ($quest) {
			$quest->stage = 1;
			$quest->save();
		}

		return [
			'type' => $type,
			'exchange' => $exchange,
		];
	}
}
