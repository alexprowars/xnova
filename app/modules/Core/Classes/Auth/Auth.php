<?php

namespace Friday\Core\Auth;

use Friday\Core\Models\Session;
use Friday\Core\Models\User;
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
 */
class Auth extends Component
{
	private $_authorized = false;

	public function isAuthorized()
	{
		return $this->_authorized;
	}

	public function getSecret ($uid, $password)
	{
		return md5 ($password.':'.$this->config->application->encryptKey.':'.$uid);
	}

	function check ()
	{
		$result = $this->checkUserAuth();

		if ($this->_authorized && $result)
			return $result;

		return false;
	}

	public function getSessionKey ()
	{
		return "session_id";
	}

	private function checkUserAuth ()
	{
		if (!$this->cookies->has($this->getSessionKey()))
			return false;

		$sessionId = $this->cookies->get($this->getSessionKey())->getValue();

		$session = Session::findFirst(["conditions" => "token = ?0", "bind" => [$sessionId]]);

		if (!$session || $session->object_type != Session::OBJECT_TYPE_USER)
		{
			$this->remove();

			return false;
		}

		$UserRow = User::findFirst($session->object_id);

		if (!$UserRow)
			$this->remove();
		else
		{
			$this->_authorized = true;

			$ip = sprintf("%u", ip2long($this->request->getClientAddress()));

			if ($UserRow->ip != $ip)
			{
				$UserRow->ip = $ip;
				$UserRow->update();
			}
		}

		return $UserRow;
	}

	public function authorize ($userId, $expire = 0)
	{
		$session = Session::start(Session::OBJECT_TYPE_USER, $userId, $expire);

		if (!$session)
			throw new \Exception("Can`t start auth session");

		$this->cookies->set($this->getSessionKey(), $session->token, $expire);

		if ($this->session->isStarted())
			$this->session->destroy(true);
	}

	public function remove ($redirect = false)
	{
		$this->session->destroy(true);
		$this->cookies->get($this->getSessionKey())->delete();

		$this->_authorized = false;

		if ($redirect)
		{
			$this->response->redirect('')->send();
			die();
		}
	}
}