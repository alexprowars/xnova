<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Request;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Helpers;
use Xnova\Controller;
use Xnova\Models;
use Xnova\Vars;
use Nubs\RandomNameGenerator;

class StartController extends Controller
{
	public function index ()
	{
		if ($this->user->sex == 0 || $this->user->avatar == 0)
		{
			if (Request::post('save'))
			{
				$username = strip_tags(trim(Request::post('character')));

				if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-!~.@ ]+$/u", $username))
					throw new ErrorException(__('start.error_charalpha'));

				$ExistUser = Models\Users::query()
					->where('username', $username)
					->where('id', '!=', $this->user->getId())
					->exists();

				if ($ExistUser)
					throw new ErrorException(__('reg.error_userexist'));

				$face = Helpers::checkString(Request::post('face', ''));

				if ($face != '')
				{
					$face = explode('_', $face);

					$face[0] = (int) $face[0];

					if ($face[0] != 1 && $face[0] != 2)
					{
						$this->user->sex = 0;
						$this->user->avatar = 1;
					}
					else
					{
						$face[1] = (int) $face[1];

						if ($face[1] < 1 || $face[1] > 8)
							$face[1] = 1;

						$this->user->sex = (int) $face[0];
						$this->user->avatar = (int) $face[1];
					}
				}

				$this->user->username = $username;
				$this->user->update();
			}

			if ($this->user->username == '')
			{
				$generator = RandomNameGenerator\All::create();
				$this->user->username = $generator->getName();
			}
		}
		elseif ($this->user->race == 0)
		{
			if (Request::post('save'))
			{
				$r = (int) Request::post('race', 0);
				$r = ($r < 1 || $r > 4) ? 0 : $r;

				if ($r <= 0)
					throw new ErrorException('Выберите фракцию');

				$this->user->race = $r;
				$this->user->bonus = time() + 86400;

				foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $oId)
					$this->user->{Vars::getName($oId)} = time() + 86400;

				$this->user->update();

				throw new RedirectException('', '/tutorial/');
			}
		}

		$races = [];

		foreach (__('main.race') as $i => $race)
		{
			if ($i === 0)
				continue;

			$races[] = [
				'i' => $i,
				'name' => $race,
				'description' => __('infos.info.'.(700 + $i)),
			];
		}

		$this->setTitle('Выбор персонажа');

		$this->setViews('menu', false);
		$this->setViews('header', false);
		$this->showTopPanel(false);

		return [
			'sex' => (int) $this->user->sex,
			'avatar' => (int) $this->user->avatar,
			'race' => (int) $this->user->race,
			'races' => $races,
			'name' => $this->user->username
		];
	}
}