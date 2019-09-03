<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $password
 * @property $email
 * @property $name
 * @property $last_name
 * @property $second_name
 * @property $gender
 * @property $photo
 * @property $create_time
 * @property $about
 * @property $settings
 * @property $image
 * @property $username_last
 * @property $fleet_shortcut
 * @property string $birthday
 * @property int $free_race_change
 */
class UsersInfo extends Model
{
	public $timestamps = false;
	public $table = 'users_info';

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