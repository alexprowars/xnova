<?php
namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * Class User
 * @package App\Models
 * @property \Phalcon\Db\Adapter\Pdo\Mysql db
 */
class User extends Model
{
	private $optionsData = array('security' => 0);

	public $id;
	public $username;
	public $authlevel;
	public $onlinetime;
	public $banned;
	public $options;

	public function onConstruct()
	{

	}

	public function isAdmin()
	{
		if ($this->id > 0)
			return ($this->authlevel == 3);
		else
			return false;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getSource()
	{
		return "game_users";
	}

	public function isOnline ()
	{
		return (time() - $this->onlinetime < 180);
	}


	public function unpackOptions ($opt, $isToggle = true)
	{
		$result = array();

		if ($isToggle)
		{
			$o = array_reverse(str_split(decbin($opt)));

			$i = 0;

			foreach ($this->optionsData as $k => $v)
			{
				$result[$k] = (isset($o[$i]) ? $o[$i] : 0);

				$i++;
			}
		}

		return $result;
	}

	public function packOptions ($opt, $isToggle = true)
	{
		if ($isToggle)
		{
			$r = array();

			foreach ($this->optionsData as $k => $v)
			{
				if (isset($opt[$k]))
					$v = $opt[$k];

				$r[] = $v;
			}

			return bindec(implode('', array_reverse($r)));
		}
		else
			return 0;
	}
}

?>