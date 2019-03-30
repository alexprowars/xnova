<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Helpers;
use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Request;
use Xnova\Vars;

/**
 * @RoutePrefix("/start")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class StartController extends Controller
{
	public function initialize()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('reg', 'xnova');
	}

	public function indexAction ()
	{
		if ($this->user->sex == 0 || $this->user->avatar == 0)
		{
			if ($this->request->hasPost('save'))
			{
				$username = strip_tags(trim($this->request->getPost('character')));

				if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-\!\~\.@ ]+$/u", $username))
					throw new ErrorException(_getText('error_charalpha'));

				$ExistUser = $this->db->query("SELECT `id` FROM game_users WHERE `username` = '".$username."' AND id != ".$this->user->getId()." LIMIT 1")->fetch();

				if (isset($ExistUser['id']))
					throw new ErrorException(_getText('error_userexist'));

				$face = Helpers::checkString($this->request->getPost('face', 'string', ''));

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
				$generator = \Nubs\RandomNameGenerator\All::create();
				$this->user->username = $generator->getName();
			}
		}
		elseif ($this->user->race == 0)
		{
			if ($this->request->hasPost('save'))
			{
				$r = (int) $this->request->getPost('race', 'int', 0);
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

		Lang::includeLang('infos', 'xnova');

		$races = [];

		foreach (_getText('race') as $i => $race)
		{
			if ($i === 0)
				continue;

			$races[] = [
				'i' => $i,
				'name' => $race,
				'description' => _getText('info', 700 + $i),
			];
		}

		Request::addData('page', [
			'sex' => (int) $this->user->sex,
			'avatar' => (int) $this->user->avatar,
			'race' => (int) $this->user->race,
			'races' => $races,
			'name' => $this->user->username
		]);

		$this->tag->setTitle('Выбор персонажа');

		$this->setViews('menu', false);
		$this->setViews('header', false);
		$this->showTopPanel(false);
	}
}