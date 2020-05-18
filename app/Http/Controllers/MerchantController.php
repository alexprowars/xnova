<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Http\Request;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models;
use Xnova\Vars;

class MerchantController extends Controller
{
	protected $loadPlanet = true;

	private $modifiers = [
		'metal' => 1,
		'crystal' => 2,
		'deuterium' => 4,
	];

	public function index(Request $request)
	{
		if ($request->has('exchange')) {
			$this->exchange($request);
		}

		$this->setTitle('Торговец');

		return [
			'modifiers' => $this->modifiers
		];
	}

	private function exchange(Request $request)
	{
		if ($this->user->credits <= 0) {
			throw new ErrorException('Недостаточно кредитов для проведения обменной операции');
		}

		$metal = (int) $request->post('metal', 0);
		$crystal = (int) $request->post('crystal', 0);
		$deuterium = (int) $request->post('deuterium', 0);

		if ($metal < 0 || $crystal < 0 || $deuterium < 0) {
			throw new ErrorException('Злобный читер');
		}

		$type = trim($request->post('type'));

		if (!in_array($type, Vars::getResources())) {
			throw new ErrorException('Ресурс не существует');
		}

		$exchange = 0;

		foreach (Vars::getResources() as $res) {
			if ($res != $type) {
				$exchange += $$res * ($this->modifiers[$res] / $this->modifiers[$type]);
			}
		}

		if ($exchange <= 0) {
			throw new ErrorException('Вы не можете обменять такое количество ресурсов');
		}

		if ($this->planet->{$type} < $exchange) {
			throw new ErrorException('На планете недостаточно ресурсов данного типа');
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

		$tutorial = Models\UserQuest::query()
			->select(['id'])
			->where('user_id', $this->user->getId())
			->where('quest_id', 6)
			->where('finish', 0)
			->where('stage', 0)
			->first();

		if ($tutorial) {
			$tutorial->stage = 1;
			$tutorial->update();
		}

		throw new RedirectException('Вы обменяли ' . $exchange . ' ' . __('main.res.' . $type), '/merchant/');
	}
}
