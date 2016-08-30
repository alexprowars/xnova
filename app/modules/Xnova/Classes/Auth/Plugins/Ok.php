<?php

namespace Xnova\Auth\Plugins;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Options;
use Xnova\Helpers;
use Phalcon\Mvc\User\Component;

class Ok extends Component implements AuthInterface
{
	private $isLogin = false;
	private $data = [];
	
	private $api_url = '';
	private $api_key = '';

	public function check ()
	{
		if ($this->request->hasPost('application_key') && $this->request->hasPost('api_server'))
		{
			$this->connect($this->request->getPost('application_key'), $this->request->getPost('api_server'));

			$uInfo = $this->send('users/getInfo', 
			[
				'uids'		=> $this->request->getPost('logged_user_id', 'int', 0),
				'fields'	=> 'first_name,last_name,name,gender,birthday,age,locale,location,current_location,online,pic128x128'
			]);
			
			$this->data = $uInfo[0];
			$this->isLogin = true;
			$this->login();
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

		if (md5($_POST['logged_user_id'].$_POST['session_key'].$this->config->ok->private) != $_POST['auth_sig'])
			echo '<script type="text/javascript">alert("Параметры авторизации являются некорректными!")</script>';
		else
		{
			$Row = $this->db->query("SELECT u.id, u.tutorial, a.id AS auth_id FROM game_users u, game_users_info ui, game_users_auth a WHERE ui.id = u.id AND a.user_id = u.id AND a.external_id = 'http://www.odnoklassniki.ru/profile/".intval($_POST['logged_user_id'])."';")->fetch();

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

			$this->session->set('OKAPI', $_POST);

			if ($this->cookies->has($this->config->cookie->prefix."_full"))
				$this->cookies->get($this->config->cookie->prefix."_full")->setDomain($this->request->getServer('SERVER_NAME'))->delete();

			echo '<center>Загрузка...-</center><script>parent.location.href="'.$this->url->getBaseUri().'overview/?'.http_build_query($_POST).'";</script>';
		}
		
		die();
	}

	public function register ()
	{
		$uid = intval($_POST['logged_user_id']);

		if (!$uid)
			return false;

		if (isset($_POST['custom_args']))
		{
			parse_str($_POST['custom_args'], $cArgs);

			$refer = (isset($cArgs['userId']) ? intval($cArgs['userId']) : 0);
		}
		else
			$refer = 0;

		$NewPass = Helpers::randomSequence();

		if ($refer != 0)
		{
			$refe = $this->db->query("SELECT id FROM game_users_info WHERE id = '".$refer."'")->fetch();

			if (!isset($refe['id']))
				$refer = 0;
		}
		
		$check = $this->db->query("SELECT user_id FROM game_users_auth WHERE external_id = 'http://www.odnoklassniki.ru/profile/".$uid."'")->fetch();
		
		if (isset($check['user_id']))
		{
			$find = $this->db->query("SELECT id FROM game_users WHERE id = ".$check['user_id']."")->fetch();

			if (!isset($find['id']))
				$this->db->query("DELETE FROM game_users_auth WHERE user_id = ".$check['user_id']."");
			else
				return false;
		}

		$this->db->query("LOCK TABLES game_users_info WRITE, game_users WRITE, game_users_auth WRITE");

		$this->db->insertAsDict(
		   	"game_users",
			[
				'username' 		=> addslashes(str_replace('\'', '', $this->data['name'])),
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
					'password' 		=> md5($NewPass)
			   	]
			);

			$this->db->insertAsDict(
			   	"game_users_auth",
				[
					'user_id' 		=> $iduser,
					'external_id' 	=> 'http://www.odnoklassniki.ru/profile/'.$uid,
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

	public function connect ($api_key, $api_url)
	{
		if (strpos($api_url, 'ok.ru') === false)
			$api_url = $this->config->ok->api;

		$this->api_url = $api_url;
		$this->api_key = $api_key;
	}

	public function send ($method, $params = [])
	{
		if (!is_array($params))
			$params = [];

		$params['application_key'] = $this->api_key;
		$params['format'] = 'JSON';

		if (isset($params['session_secret_key']))
		{
			$signature = $params['session_secret_key'];
			unset($params['session_secret_key']);
		}
		else
			$signature = $this->config->ok->private;

		ksort($params);

		$sig = '';

		foreach($params as $k => $v)
			$sig .= $k.'='.$v;

		$sig .= $signature;

		$params['sig'] = md5($sig);

		$res = file_get_contents($this->api_url.'api/'.$method.'?'.http_build_query($params));

		return json_decode($res, true);
	}
}

?>