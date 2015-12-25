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

	public function sendMessage ($owner, $sender, $time, $type, $from, $message)
	{
		if (!$time)
			$time = time();

		if (!$owner && $this->auth->isAuthorized())
			$owner = $this->data['id'];

		if (!$owner)
			return false;

		if ($sender === false && $this->auth->isAuthorized())
			$sender = $this->data['id'];

		if ($this->auth->isAuthorized() && $owner == $this->user->getId())
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
}

?>