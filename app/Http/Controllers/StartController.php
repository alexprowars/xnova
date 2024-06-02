<?php

namespace App\Http\Controllers;

use App\Engine\Vars;
use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Helpers;
use App\Models;
use Illuminate\Http\Request;

class StartController extends Controller
{
	public function save(Request $request)
	{
		if ($this->user->sex && $this->user->avatar) {
			throw new RedirectException('/');
		}

		$data = $request->validate([
			'name' => 'required|string',
			'avatar' => 'required|string',
		]);

		$data['name'] = strip_tags(trim($data['name']));

		if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-!~.@ ]+$/u", $data['name'])) {
			throw new Exception(__('start.error_charalpha'));
		}

		$existUser = Models\User::query()
			->where('username', $data['name'])
			->where('id', '!=', $this->user->id)
			->exists();

		if ($existUser) {
			throw new Exception(__('reg.error_userexist'));
		}

		$this->user->username = $data['name'];

		$face = Helpers::checkString($data['avatar']);

		if (!empty($face)) {
			$face = explode('_', $face);
			$face[0] = (int) $face[0];

			if ($face[0] != 1 && $face[0] != 2) {
				$this->user->sex = 0;
				$this->user->avatar = 1;
			} else {
				$face[1] = (int) $face[1];

				if ($face[1] < 1 || $face[1] > 8) {
					$face[1] = 1;
				}

				$this->user->sex = $face[0];
				$this->user->avatar = $face[1];
			}
		}

		$this->user->update();
	}

	public function race(Request $request)
	{
		if ($this->user->race) {
			throw new RedirectException('/');
		}

		$request->validate([
			'race' => 'required|numeric',
		]);

		$r = (int) $request->post('race', 0);
		$r = ($r < 1 || $r > 4) ? 0 : $r;

		if ($r <= 0) {
			throw new Exception('Выберите фракцию');
		}

		$this->user->race = $r;
		$this->user->daily_bonus = now()->addDay();

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $oId) {
			$this->user->{Vars::getName($oId)} = now()->addDay();
		}

		$this->user->update();
	}
}
