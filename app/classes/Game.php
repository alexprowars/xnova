<?php
namespace App;

use Phalcon\Mvc\User\Component;

class Game extends Component
{
	function datezone ($format, $time = 0)
	{
		if ($time == 0)
			$time = time();

		return date($format, $time);
	}
}

?>