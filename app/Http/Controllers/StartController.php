<?php

namespace App\Http\Controllers;

use App\Exceptions\RedirectException;
use Illuminate\Http\Request;
use App\Exceptions\ErrorException;
use App\Helpers;
use App\Controller;
use App\Models;
use App\Vars;

class StartController extends Controller
{
	public function save(Request $request)
	{
		if (!$this->user->sex || !$this->user->avatar) {
			if ($request->has('username')) {
				$username = strip_tags(trim($request->post('username')));

				if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-!~.@ ]+$/u", $username)) {
					throw new ErrorException(__('start.error_charalpha'));
				}

				$existUser = Models\User::query()
					->where('username', $username)
					->where('id', '!=', $this->user->id)
					->exists();

				if ($existUser) {
					throw new ErrorException(__('reg.error_userexist'));
				}

				$this->user->username = $username;
			}

			if ($request->has('avatar')) {
				$face = Helpers::checkString($request->post('avatar'));

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
			}

			$this->user->update();
		}

		if (!$this->user->race && $request->has('race')) {
			$r = (int) $request->post('race', 0);
			$r = ($r < 1 || $r > 4) ? 0 : $r;

			if ($r <= 0) {
				throw new ErrorException('Выберите фракцию');
			}

			$this->user->race = $r;
			$this->user->bonus = time() + 86400;

			foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $oId) {
				$this->user->{Vars::getName($oId)} = time() + 86400;
			}

			$this->user->update();

			throw new RedirectException('', '/tutorial');
		}
	}
}
