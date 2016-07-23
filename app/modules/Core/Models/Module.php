<?php

namespace Friday\Core\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Module[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Module findFirst(mixed $parameters = null)
 */
class Module extends Model
{
	public $id;
	public $code;
	public $sort;
	public $system;
	public $active;

	public function getSource()
	{
		return DB_PREFIX."modules";
	}

	public function validation ()
	{
		if ($this->code == 'core' && $this->active == VALUE_FALSE)
		{
			$this->appendMessage(new Message('Модуль ядра не может быть деактивирован', 'active', 'error'));

			return false;
		}

		return true;
	}
}