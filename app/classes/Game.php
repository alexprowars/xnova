<?php
namespace App;

use Phalcon\Mvc\User\Component;

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
 * @property \App\Models\User user
 * @property \App\Auth\Auth auth
 * @property \Phalcon\Mvc\Dispatcher dispatcher
 * @property \Phalcon\Flash\Direct flash
 * @property \Phalcon\Config|\stdClass config
 * @property \App\Game game
 */
class Game extends Component
{
	private $message = '';
	private $status = 1;
	private $data = [];

	public $planet;

	public $resource 		= [];
	public $requeriments 	= [];
	public $pricelist 		= [];
	public $gun_armour 		= [];
	public $CombatCaps 		= [];
	public $ProdGrid 		= [];
	public $reslist 		= [];

	function datezone ($format, $time = 0)
	{
		if ($time == 0)
			$time = time();

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
		require_once(APP_PATH."app/varsGlobal.php");

		/** @var array $resource */
		/** @var array $requeriments */
		/** @var array $pricelist */
		/** @var array $gun_armour */
		/** @var array $CombatCaps */
		/** @var array $ProdGrid */
		/** @var array $reslist */

		$this->resource 	= $resource;
		$this->requeriments = $requeriments;
		$this->pricelist 	= $pricelist;
		$this->gun_armour 	= $gun_armour;
		$this->CombatCaps 	= $CombatCaps;
		$this->ProdGrid 	= $ProdGrid;
		$this->reslist 		= $reslist;
	}

	public function sendMessage ($owner, $sender, $time, $type, $from, $message)
	{
		if (!$time)
			$time = time();

		if (!$owner && isset($this->auth) && $this->auth->isAuthorized())
			$owner = $this->user->id;

		if (!$owner)
			return false;

		if ($sender === false && isset($this->auth) && $this->auth->isAuthorized())
			$sender = $this->user->id;
		else
			$sender = 0;

		if (isset($this->auth) && $this->auth->isAuthorized() && $owner == $this->user->getId())
			$this->user->messages++;

		$this->db->insertAsDict(
			"game_messages",
			array
			(
				'message_owner'		=> $owner,
				'message_sender'	=> $sender,
				'message_time'		=> $time,
				'message_type'		=> $type,
				'message_from'		=> addslashes($from),
				'message_text'		=> addslashes($message)
			)
		);

		$this->db->query("UPDATE game_users SET messages = messages + 1 WHERE id = ".$owner."");

		return true;
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

	public function updateConfig ($key, $value)
	{
		$this->db->query("UPDATE game_config SET `value` = '". $value ."' WHERE `key` = '".$key."';");
		$this->config->app->offsetSet($key, $value);
	}
}

?>