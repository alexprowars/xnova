<?php
namespace App\Controllers;

class StartController extends ApplicationController
{
	public function initialize()
	{
		parent::initialize();
	}

	public function indexAction ()
	{
		$error = '';

		if (user::get()->data['sex'] == 0 || user::get()->data['avatar'] == 0)
		{
			if (isset($_POST['save']))
			{
				strings::includeLang('reg');

				$arUpdate = array();

				if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-\!\~\.@ ]+$/u", $_POST['character']))
				{
					$error .= _getText('error_charalpha');
				}

				$ExistUser = db::query("SELECT `id` FROM game_users WHERE `username` = '" . db::escape_string(trim($_POST['character'])) . "' AND id != ".user::get()->getId()." LIMIT 1;", true);

				if (isset($ExistUser['id']))
				{
					$error .= _getText('error_userexist');
				}

				$face = strings::CheckString($_POST['face']);

				if ($face != '')
				{
					$face = explode('_', $face);

					$face[0] = intval($face[0]);

					if ($face[0] != 1 && $face[0] != 2)
					{
						$arUpdate['sex'] = 0;
						$arUpdate['avatar'] = 1;
					}
					else
					{
						$face[1] = intval($face[1]);

						if ($face[1] < 1 || $face[1] > 8)
							$face[1] = 1;

						$arUpdate['sex'] = $face[0];
						$arUpdate['avatar'] = $face[1];
					}
				}

				if (!$error)
				{
					$arUpdate['username'] = db::escape_string(strip_tags(trim($_POST['character'])));

					sql::build()->update('game_users')->set($arUpdate)->where('id', '=', user::get()->getId())->execute();

					request::redirectTo('?set=overview');
				}
			}

			$this->setTemplate('start/start_sex');

			$this->set('name', user::get()->data['username']);
		}
		elseif (user::get()->data['race'] == 0)
		{
			strings::includeLang('infos');

			$this->setTemplate('start/start_race');

			if (isset($_POST['save']))
			{
				$r = request::P('race', 0, VALUE_INT);
				$r = ($r < 1 || $r > 4) ? 0 : $r;

				if ($r != 0)
				{
					global $reslist, $resource;

					sql::build()->update('game_users')->set(Array('race' => $r, 'bonus' => (time() + 86400)));

					foreach ($reslist['officier'] AS $oId)
						sql::build()->setField($resource[$oId], (time() + 86400));

					sql::build()->where('id', '=', user::get()->getId())->execute();

					request::redirectTo("?set=tutorial");
				}
				else
					$error = 'Выберите фракцию';
			}
		}

		$this->set('error', $error);

		$this->setTitle('Выбор персонажа');
		$this->showTopPanel(false);
		//$this->showLeftPanel(false);
		$this->display();
	}
}

?>