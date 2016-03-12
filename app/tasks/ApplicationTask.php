<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

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