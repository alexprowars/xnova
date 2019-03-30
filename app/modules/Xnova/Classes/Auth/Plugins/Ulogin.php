<?php

namespace Xnova\Auth\Plugins;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Options;
use Phalcon\Mvc\User\Component;
use Xnova\Exceptions\RedirectException;

class Ulogin extends Component implements AuthInterface
{
	private $token = '';
	private $data = [];
	private $isLogin = false;

	public function check ()
	{
		if ($this->request->has('token') && $this->request->get('token') != '' && $this->router->getControllerName() != 'options')
		{
			$token = $this->request->get('token');

			$s = file_get_contents('http://u-login.com/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST']);
			$this->data = json_decode($s, true);

			$this->token = $token;

			if (isset($this->data['identity']))
			{
				$this->isLogin = true;
				$this->login();
			}
		}
	}

	public function isAuthorized ()
	{
		return $this->isLogin;
	}

	public function login ()
	{
		if (!$this->isAuthorized())
			return false;

		$identity = isset($this->data['profile']) && $this->data['profile'] != '' ? $this->data['profile'] : $this->data['identity'];
		$identity = str_replace(['http://', 'https://'], '', $identity);

		$Row = $this->db->query("SELECT u.id, ui.password, a.id AS auth_id FROM game_users u, game_users_info ui, game_users_auth a WHERE ui.id = u.id AND a.user_id = u.id AND a.external_id LIKE '%".$identity."';")->fetch();

		if (!isset($Row['id']) && strpos($identity, 'ok.ru') !== false)
		{
			$identity = str_replace('ok.ru', 'www.odnoklassniki.ru', $identity);

			$Row = $this->db->query("SELECT u.id, ui.password, a.id AS auth_id FROM game_users u, game_users_info ui, game_users_auth a WHERE ui.id = u.id AND a.user_id = u.id AND a.external_id LIKE '%".$identity."';")->fetch();
		}

		if (!isset($Row['id']))
			$this->register();
		else
		{
			$this->db->updateAsDict(
			   	"game_users_auth",
				['enter_time' => time()],
			   	"id = ".$Row['auth_id']
			);

			$this->auth->authorize($Row['id'], (time() + 2419200));
		}

		throw new RedirectException('', '/overview/');
	}

	public function register ()
	{
		$identity = isset($this->data['profile']) && $this->data['profile'] != '' ? $this->data['profile'] : $this->data['identity'];

		$check = $this->db->query("SELECT user_id FROM game_users_auth WHERE external_id = '".$identity."'")->fetch();

		if (isset($check['user_id']))
		{
			$find = $this->db->query("SELECT id FROM game_users WHERE id = ".$check['user_id']."")->fetch();

			if (!isset($find['id']))
				$this->db->delete('game_users_auth', 'user_id = ?', [$check['user_id']]);
			else
				return false;
		}

		$refer = (isset($_SESSION['ref']) ? intval($_SESSION['ref']) : 0);

		if ($refer != 0)
		{
			$refe = $this->db->query("SELECT id FROM game_users_info WHERE id = '".$refer."'")->fetch();

			if (!isset($refe['id']))
				$refer = 0;
		}

		$this->db->query("LOCK TABLES game_users_info WRITE, game_users WRITE, game_users_auth WRITE");

		$this->db->insertAsDict(
		   	"game_users",
			[
				'username' 		=> trim($this->data['first_name']." ".$this->data['last_name']),
				'sex' 			=> 0,
				'ip' 			=> sprintf("%u", ip2long($this->request->getClientAddress())),
				'bonus' 		=> time(),
				'onlinetime' 	=> time(),
				'planet_id'		=> 0
		   	]
		);

		$iduser = $this->db->lastInsertId();

		if ($iduser > 0)
		{
			$this->db->insertAsDict(
			   	"game_users_info",
				[
					'id' 			=> $iduser,
					'email' 		=> '',
					'create_time' 	=> time(),
					'password' 		=> md5($this->token)
			   	]
			);

			$this->db->insertAsDict(
			   	"game_users_auth",
				[
					'user_id' 		=> $iduser,
					'external_id' 	=> isset($this->data['profile']) && $this->data['profile'] != '' ? $this->data['profile'] : $this->data['identity'],
					'create_time' 	=> time(),
					'enter_time' 	=> time()
			   	]
			);

			$this->db->query("UNLOCK TABLES");

			if ($refer != 0)
			{
				$ref = $this->db->query("SELECT id FROM game_users_info WHERE id = '".$refer."'")->fetch();

				if (isset($ref['id']))
				{
					$this->db->insertAsDict(
					   	"game_refs",
						[
							'r_id' 	=> $iduser,
							'u_id'	=> $refer
					   	]
					);
				}
			}

			$total = $this->db->query("SELECT `value` FROM game_options WHERE `name` = 'users_total'")->fetch();

			Options::set('users_total', $total['value'] + 1);

			$this->auth->authorize($iduser);

			return true;
		}
		else
		{
			$this->db->query("UNLOCK TABLES");

			return false;
		}
	}
}