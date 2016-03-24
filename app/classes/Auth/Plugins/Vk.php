<?php

namespace App\Auth\Plugins;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\User\Component;

class Vk extends Component implements AuthInterface
{
	private $isLogin = false;

	public function check ()
	{
		if ($this->request->hasPost('viewer_id') && $this->request->hasPost('auth_key'))
		{
			if (md5($this->config->vk->id."_".$this->request->getPost('viewer_id', 'int')."_".$this->config->vk->secret) == $this->request->getPost('auth_key'))
			{
				$uInfo = $this->send('users.get', array('user_ids' => $_POST['viewer_id'], 'fields' => 'sex'));

				$this->data = $uInfo['response'][0]['user'];

				if (count($uInfo['response']))
				{
					$this->isLogin = true;
					$this->login();
				}
				else
					die('<script type="text/javascript">alert("Параметры авторизации являются некорректными! #1")</script>');
			}
			else
				die('<script type="text/javascript">alert("Параметры авторизации являются некорректными! #2")</script>');
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

		$Row = $this->db->query("SELECT u.id, u.tutorial, ui.password, a.id AS auth_id FROM game_users u, game_users_info ui, game_users_auth a WHERE ui.id = u.id AND a.user_id = u.id AND a.external_id = 'http://vk.com/id".$this->request->getPost('viewer_id', 'int')."';")->fetch();

		if (!isset($Row['id']))
			$this->register();
		else
		{
			$this->db->updateAsDict(
			   	"game_users_auth",
				['enter_time' => time()],
			   	"id = ".$Row['auth_id']
			);

			$this->auth->auth($Row['id'], $Row['password'], 0, (time() + 2419200));
		}

		echo '<center>Загрузка...</center><script>parent.location.href="/overview/?'.http_build_query($_POST).'";</script>';
		die();
	}

	public function register ()
	{
		die('fff');
	}

	private function send ($method, $params = array())
	{
		$params['api_id'] 		= $this->config->vk->id;
		$params['method'] 		= $method;
		$params['timestamp'] 	= time() + 100;
		$params['format'] 		= 'json';
		$params['random'] 		= rand(0,10000);

		ksort($params);

		$sig = '';

		foreach($params as $k => $v)
			$sig .= trim($k).'='.trim($v);

		$params['sig'] = md5($sig.$this->config->vk->secret);

		return json_decode(file_get_contents($this->config->vk->api.'?'.http_build_query($params, null, '&')), true);
	}
}

?>