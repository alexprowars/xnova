<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\User\Component;
use Xnova\Models\User;

/**
 * Class ControllerBase
 * @property \Phalcon\Mvc\View view
 * @property \Phalcon\Tag tag
 * @property \Phalcon\Assets\Manager assets
 * @property \Phalcon\Db\Adapter\Pdo\Mysql db
 * @property \Phalcon\Mvc\Model\Manager modelsManager
 * @property \Phalcon\Session\Adapter\Memcache session
 * @property \Phalcon\Http\Response\Cookies cookies
 * @property \Phalcon\Http\Request request
 * @property \Phalcon\Http\Response response
 * @property \Phalcon\Mvc\Router router
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \Phalcon\Mvc\Url url
 * @property \Xnova\Models\User user
 * @property \Friday\Core\Auth\Auth auth
 * @property \Phalcon\Mvc\Dispatcher dispatcher
 * @property \Phalcon\Flash\Direct flash
 * @property \Phalcon\Registry|\stdClass storage
 * @property \Phalcon\Config|\stdClass config
 * @property \Xnova\Game game
 */
class Game extends Component
{
	private $message = '';
	private $status = 1;
	private $data = [];

	public $planet;

	function datezone ($format, $time = 0)
	{
		if ($time == 0)
			$time = time();

		if ($this->di->has('user') && !is_null($this->user->timezone))
			$time += $this->user->timezone * 1800;

		return date($format, $time);
	}

	public function setRequestMessage ($message = '')
	{
		$this->message = $message;
	}

	public function getRequestMessage ()
	{
		return $this->message;
	}

	public function setRequestStatus ($status = '')
	{
		$this->status = $status;
	}

	public function getRequestStatus ()
	{
		return $this->status;
	}

	public function setRequestData ($data = [])
	{
		if (is_array($data))
			$this->data = $data;
	}

	public function getRequestData ()
	{
		return $this->data;
	}

	public function getSpeed ($type = '')
	{
		if ($type == 'fleet')
			return $this->config->game->get('fleet_speed', 2500) / 2500;
		if ($type == 'mine')
			return $this->config->game->get('resource_multiplier', 1);
		if ($type == 'build')
			return round($this->config->game->get('game_speed', 2500) / 2500, 1);

		return 1;
	}

	public function loadGameVariables ()
	{
		require_once(ROOT_PATH."/app/varsGlobal.php");

		/** @var array $resource */
		/** @var array $requeriments */
		/** @var array $pricelist */
		/** @var array $gun_armour */
		/** @var array $CombatCaps */
		/** @var array $ProdGrid */
		/** @var array $reslist */

		$this->registry->resource = $resource;
		$this->registry->resource_flip = array_flip($resource);
		$this->registry->requeriments = $requeriments;
		$this->registry->pricelist = $pricelist;
		$this->registry->gun_armour = $gun_armour;
		$this->registry->CombatCaps = $CombatCaps;
		$this->registry->ProdGrid = $ProdGrid;
		$this->registry->reslist = $reslist;
	}

	public function checkSaveState ()
	{
		return (!($this->request->get('ep', null, '') == 'dontsavestate'));
	}

	public function getClearQuery ()
	{
		/*$out = $this->request->getQuery();

		unset($out['ajax']);
		unset($out['popup']);
		unset($out['random']);
		unset($out['_']);
		unset($out['_url']);
		unset($out['isAjax']);

		if (count($out))
			return $this->router->getRewriteUri().'?'.http_build_query($out);
		else*/
			return $this->router->getRewriteUri();
	}

	public function checkReferLink ()
	{
		if (!$this->session->has('uid') && is_numeric($this->request->getServer('QUERY_STRING')) && strlen($this->request->getServer('QUERY_STRING')) > 0)
		{
			$id = intval($this->request->getServer('QUERY_STRING'));

			$user = User::findFirst($id);

			if ($user)
			{
				$ip = sprintf("%u", ip2long($this->request->getClientAddress()));

				$res = $this->db->fetchOne("SELECT `id` FROM game_moneys where `ip` = '" . $ip . "' AND `time` > '" . (time() - 86400) . "'");

				if (!isset($res['id']))
				{
					$this->db->insertAsDict(
						"game_moneys",
						[
							'user_id'		=> $user->id,
							'time'			=> time(),
							'ip'			=> $ip,
							'referer'		=> $this->request->hasServer('HTTP_REFERER') ? $this->request->getServer('HTTP_REFERER') : '',
							'user_agent'	=> $this->request->getServer('HTTP_USER_AGENT'),
						]
					);

					$user->links++;
					$user->refers++;
					$user->update();
				}

				$this->session->set('ref', $user->id);
			}
		}
	}
}