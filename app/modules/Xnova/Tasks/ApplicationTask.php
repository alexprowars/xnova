<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

/**
 * @property \Xnova\Database db
 * @property \Phalcon\Config|\stdClass config
 * @property \Xnova\Game game
 */
class ApplicationTask extends \Phalcon\Cli\Task
{
	public function mainAction()
	{
		echo "Enter your command...\n";
	}
}