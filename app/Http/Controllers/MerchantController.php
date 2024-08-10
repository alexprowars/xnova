<?php

namespace App\Http\Controllers;

use App\Engine\Vars;
use App\Exceptions\Exception;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
	protected $modifiers = [
		'metal' => 1,
		'crystal' => 2,
		'deuterium' => 4,
	];

	public function index()
	{
		return [
			'modifiers' => $this->modifiers,
		];
	}

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

		$exchange = 0;

		foreach (Vars::getResources() as $res) {
			if ($res != $type) {
				$exchange += $$res * ($this->modifiers[$res] / $this->modifiers[$type]);
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

		$tutorial = $this->user->quests()
			->where('quest_id', 6)
			->where('finish', 0)
			->where('stage', 0)
			->first();

		if ($tutorial) {
			$tutorial->stage = 1;
			$tutorial->save();
		}

		return [
			'type' => $type,
			'exchange' => $exchange,
		];
	}
}
