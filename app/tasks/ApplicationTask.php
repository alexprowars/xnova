<?php

/**
 * @property \App\Database db
 * @property \Phalcon\Config|\stdClass config
 * @property \App\Game game
 */
class ApplicationTask extends \Phalcon\Cli\Task
{
	public function mainAction()
	{
		echo "Enter your command...\n";
	}
}

?>