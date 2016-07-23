<?php

namespace Xnova\Bot\Controllers;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class IndexController extends Application
{
	public function indexAction()
	{
		$this->notFoundAction();
	}

	public function setAction()
	{
		$hook_url = 'https://'.$this->config->app->url.'/bot/hook/';

		try
		{
			$telegram = new Telegram($this->config->telegram->token, $this->config->telegram->login);

			$result = $telegram->setWebHook($hook_url);
		
			if ($result->isOk())
				echo $result->getDescription();
		}
		catch (TelegramException $e)
		{
			echo $e;
		}
	}

	public function unsetAction()
	{
		try 
		{
			$telegram = new Telegram($this->config->telegram->token, $this->config->telegram->login);
		
			$result = $telegram->unsetWebHook();
		
			if ($result->isOk())
				echo $result->getDescription();
		}
		catch (TelegramException $e) 
		{
			echo $e;
		}
	}

	public function hookAction()
	{
		try
		{
			$telegram = new Telegram($this->config->telegram->token, $this->config->telegram->login);

			// Enable MySQL
			$telegram->enableExternalMysql($this->db->getInternalHandler(), 'bot_');
		
			// Add an additional commands path
			$telegram->addCommandsPath(APP_PATH.$this->config->application->baseDir.$this->config->application->modulesDir.'bot/commands/');
		
			// Here you can enable admin interface for the channel you want to manage
			$telegram->enableAdmins(['134099267']);

			// Logging
			$telegram->setLogRequests(true);
			$telegram->setLogPath(APP_PATH."app/logs/telegram.log");
			$telegram->setLogVerbosity(3);
		
			// Set custom Upload and Download path
			//$telegram->setDownloadPath('../Download');
			//$telegram->setUploadPath('../Upload');

			$telegram->handle();
		} 
		catch (TelegramException $e) 
		{
			// Silence is golden!
			// log telegram errors
			//echo $e;
		}
	}
}

?>