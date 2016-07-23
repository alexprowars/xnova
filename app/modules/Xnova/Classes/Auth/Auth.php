<?php
namespace App\Auth;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Models\User;
use Phalcon\Db;
use Phalcon\Mvc\User\Component;

/**
 * Class Auth
 * @property \Phalcon\Mvc\View view
 * @property \Phalcon\Tag tag
 * @property \Phalcon\Assets\Manager assets
 * @property \Phalcon\Db\Adapter\Pdo\Mysql db
 * @property \Phalcon\Session\Adapter\Memcache session
 * @property \Phalcon\Http\Response\Cookies cookies
 * @property \Phalcon\Http\Request request
 * @property \Phalcon\Http\Response response
 * @property \Phalcon\Mvc\Router router
 * @property \Phalcon\Config|\stdClass config
 * @property \App\Game game
 */
class Auth extends Component
{
	private $IsUserChecked = false;
	private $plugins = [];

	public function addAuthPlugin ($className)
	{
		$this->plugins[] = $className;
	}

	public function checkExtAuth ()
	{
		foreach ($this->plugins as $plugin)
		{
			$ext = new $plugin();
			/** @noinspection PhpUndefinedMethodInspection */
			$ext->check();
		}

		if ($this->request->has('authId') && $this->request->has('authSecret'))
		{
			$this->cookies->set($this->config->cookie->prefix.'_id', 		$this->request->get('authId', 'int'));
			$this->cookies->set($this->config->cookie->prefix.'_secret', 	$this->request->get('authSecret'));
		}
	}

	public function isAuthorized()
	{
		return $this->IsUserChecked;
	}

	public function getSecret ($uid, $password, $security = 0)
	{
		return ($security) ? md5($password."---".$this->request->getClientAddress()."---IPSECURITYFLAG_Y---".$uid) : md5($password."---IPSECURITYFLAG_N---".$uid);
	}

	function check ()
	{
		$Result = $this->checkUserAuth();

		if ($this->IsUserChecked)
		{
			if ($this->request->has('set') || $this->router->getControllerName() != 'banned')
			{
				if ($Result->banned > time())
					die('Ваш аккаунт заблокирован. Срок окончания блокировки: '.$this->game->datezone("d.m.Y H:i:s", $Result->banned).'<br>Для получения дополнительной информации зайдите <a href="/banned/">сюда</a>');
				elseif ($Result->banned > 0 && $Result->banned < time())
				{
					$this->db->delete('game_banned', 'who = ?', [$Result->id]);
					$this->db->updateAsDict('game_users', ['banned' => 0], 'id = '.$Result->id);

					$Result->banned = 0;
				}
			}

			return $Result;
		}

		return false;
	}

	private function checkUserAuth ()
	{
		$UserRow = [];

		if (!$this->session->has('uid') && $this->cookies->has($this->config->cookie->prefix.'_id') && $this->cookies->has($this->config->cookie->prefix.'_secret'))
		{
			$UserResult = $this->db->query("SELECT u.*, ui.password FROM game_users u, game_users_info ui WHERE ui.id = u.id AND u.id = '".$this->cookies->get($this->config->cookie->prefix.'_id')->getValue('int')."'");

			if ($UserResult->numRows() == 0)
				$this->remove();
			else
			{
				$raw = $UserResult->fetch();

				$UserRow = new User;
				$UserRow->assign($raw);
				$UserRow->setSnapshotData($raw);
				$UserRow->afterFetch();

				$options = $UserRow->unpackOptions($UserRow->options);

				if ($this->getSecret($UserRow->id, $raw['password'], $options['security']) != $this->cookies->get($this->config->cookie->prefix.'_secret')->getValue())
					$this->remove();
				else
				{
					$this->session->set('uid', $UserRow->id);
					$this->session->set('unm', $UserRow->username);

					$this->IsUserChecked = true;
				}
			}
		}
		elseif ($this->session->has('uid'))
		{
			if (!$this->cookies->has($this->config->cookie->prefix.'_id') || !$this->cookies->has($this->config->cookie->prefix.'_secret'))
				$this->remove();
			else
			{
				/**
				 * @var \App\Models\User $UserRow
				 */
				$UserRow = User::findFirst($this->session->get('uid'));

				if (!$UserRow)
					$this->remove();
				else
					$this->IsUserChecked = true;
			}
		}

		if ($this->IsUserChecked)
		{
			$ip = sprintf("%u", ip2long($this->request->getClientAddress()));

			if ($UserRow->onlinetime < (time() - 30) || $UserRow->ip != $ip || ($this->dispatcher->getControllerName() == "chat" && ($UserRow->onlinetime < time() - 120 || $UserRow->chat == 0)) || ($this->dispatcher->getControllerName() != "chat" && $UserRow->chat > 0))
			{
				$UserRow->onlinetime = time();

				if ($UserRow->ip != $ip)
				{
					$UserRow->ip = $ip;

					$this->db->insertAsDict(
						"game_log_ip",
						[
							'id'	=> $UserRow->id,
							'time'	=> time(),
							'ip'	=> $ip
						]
					);
				}

				//if ($this->dispatcher->getControllerName() == "chat" && $UserRow->chat == 0)
				//	$UserRow->chat = 1;
				//elseif ($this->dispatcher->getControllerName() != "chat" && $UserRow->chat > 0)
				//	$UserRow->chat = 0;

				$UserRow->update();
			}
		}

		return $UserRow;
	}

	public function auth ($userId, $password, $security = 0, $expire = 0)
	{
		$secret = $this->getSecret($userId, $password, $security);

		$this->cookies->set($this->config->cookie->prefix."_id", 		$userId, $expire, '/', 0, $_SERVER["SERVER_NAME"]);
		$this->cookies->set($this->config->cookie->prefix."_secret", 	$secret, $expire, '/', 0, $_SERVER["SERVER_NAME"]);
		$this->cookies->set($this->config->cookie->prefix."_full", 		'N', 	 $expire, '/', 0, $_SERVER["SERVER_NAME"]);
		$this->cookies->send();

		if ($this->session->isStarted())
			$this->session->destroy();
	}

	public function remove($redirect = true)
	{
		if ($this->session->isStarted())
			$this->session->destroy();

		$this->cookies->set($this->config->cookie->prefix."_id", 		null, 0, '/', 0, $_SERVER["SERVER_NAME"]);
		$this->cookies->set($this->config->cookie->prefix."_secret", 	null, 0, '/', 0, $_SERVER["SERVER_NAME"]);
		$this->cookies->set($this->config->cookie->prefix."_full", 		null, 0, '/', 0, $_SERVER["SERVER_NAME"]);
		$this->cookies->send();

		if ($redirect)
			$this->response->redirect('')->send();
	}
}