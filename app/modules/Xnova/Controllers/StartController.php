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
		try
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

					$arUpdate = [];

					$face = Helpers::checkString($this->request->getPost('face', 'string', ''));

					if ($face != '')
					{
						$face = explode('_', $face);

						$face[0] = (int) $face[0];

						if ($face[0] != 1 && $face[0] != 2)
						{
							$arUpdate['sex'] = 0;
							$arUpdate['avatar'] = 1;
						}
						else
						{
							$face[1] = (int) $face[1];

							if ($face[1] < 1 || $face[1] > 8)
								$face[1] = 1;

							$arUpdate['sex'] = $face[0];
							$arUpdate['avatar'] = $face[1];
						}
					}

					$arUpdate['username'] = $username;

					$this->user->update($arUpdate);

					throw new RedirectException('', '/start/');
				}

				$this->view->pick('shared/start/sex');

				if ($this->user->username == '')
				{
					$generator = \Nubs\RandomNameGenerator\All::create();
					$this->user->username = $generator->getName();
				}

				$this->view->setVar('name', $this->user->username);
			}
			elseif ($this->user->race == 0)
			{
				Lang::includeLang('infos', 'xnova');

				$this->view->pick('shared/start/race');

				if ($this->request->hasPost('save'))
				{
					$r = (int) $this->request->getPost('race', 'int', 0);
					$r = ($r < 1 || $r > 4) ? 0 : $r;

					if ($r <= 0)
						throw new ErrorException('Выберите фракцию');

					$update = ['race' => $r, 'bonus' => (time() + 86400)];

					foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $oId)
						$update[Vars::getName($oId)] = time() + 86400;

					$this->user->update($update);

					throw new RedirectException('', '/tutorial/');
				}
			}
		}
		catch (ErrorException $e)
		{
			$this->view->setVar('message', '');
		}

		$this->tag->setTitle('Выбор персонажа');
		$this->showTopPanel(false);
	}
}