<?php
namespace App\Models;

use Phalcon\Mvc\Model;

class Message extends Model
{
	public $id;
	public $owner;
	public $sender;
	public $time;
	public $type;
	public $deleted;
	public $from;
	public $text;

	public function getSource()
	{
		return DB_PREFIX."messages";
	}

	public function beforeCreate ()
	{
		if (!$this->time)
			$this->time = time();
	}
}

?>