<?php
namespace App;

use Phalcon\Mvc\User\Component;

class Game extends Component
{
	private $message = '';
	private $status = 1;
	private $data = [];

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
}

?>