<?php

namespace Friday\Core\Model;

use Phalcon\Events\Manager;
use Phalcon\Mvc\Model\Message;
use Phalcon\Text;

trait Events
{
	public function afterCreate ()
	{
		$this->processEventsManager('afterCreate');
	}

	public function afterUpdate ()
	{
		$this->processEventsManager('afterUpdate');
	}

	public function afterDelete ()
	{
		$this->processEventsManager('afterDelete');
	}

	public function beforeCreate ()
	{
		return $this->processEventsManager('beforeCreate', true);
	}

	public function beforeUpdate ()
	{
		return $this->processEventsManager('beforeUpdate', true);
	}

	public function beforeDelete ()
	{
		return $this->processEventsManager('beforeDelete', true);
	}

	private function processEventsManager ($type, $hasResult = false)
	{
		/**
		 * @var $eventsManager Manager
		 */
		$eventsManager = $this->getDI()->getShared('eventsManager');

		$className = Text::lower((new \ReflectionClass($this))->getShortName());

		$result = $eventsManager->fire('models:'.$className.':'.$type, $this);

		if ($hasResult)
		{
			if (is_null($result))
				return true;

			if (is_bool($result))
				return $result;
			elseif ($result instanceof Message)
				$this->appendMessage($result);
			elseif (is_array($result))
			{
				foreach ($result as $items)
				{
					if ($items instanceof Message)
						$this->appendMessage($items);
				}
			}

			return false;
		}

		return true;
	}
}