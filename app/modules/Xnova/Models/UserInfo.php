<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Database;
use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static UserInfo[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static UserInfo findFirst(mixed $parameters = null)
 * @method Database getWriteConnection
 */
class UserInfo extends Model
{
	public $id;
	public $password;
	public $email;
	public $name;
	public $last_name;
	public $second_name;
	public $gender;
	public $photo;
	public $create_time;
	public $about;
	public $settings;
	public $image;
	public $username_last;

	public function getSource()
	{
		return DB_PREFIX."users_info";
	}

	public function onConstruct()
	{
		$this->useDynamicUpdate(true);
	}

	public function getSettings ()
	{
		$data = json_decode($this->settings, true);

		if (!is_array($data))
			$data = [];

		return $data;
	}

	public function setSetting ($key, $value)
	{
		$data = $this->getSettings();
		$data[$key] = $value;

		$this->setSettings($data);
	}

	public function setSettings ($data)
	{
		if (!is_array($data))
			$data = [];

		$this->settings = json_encode($data);
	}
}