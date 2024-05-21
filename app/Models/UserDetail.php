<?php

namespace App\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
	public $timestamps = false;
	protected $guarded = [];
	public $table = 'user_details';

	public function getSettings()
	{
		$data = json_decode($this->settings, true);

		if (!is_array($data)) {
			$data = [];
		}

		return $data;
	}

	public function setSetting($key, $value)
	{
		$data = $this->getSettings();
		$data[$key] = $value;

		$this->setSettings($data);
	}

	public function setSettings($data)
	{
		if (!is_array($data)) {
			$data = [];
		}

		$this->settings = json_encode($data);
	}
}
