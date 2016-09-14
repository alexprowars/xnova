<?php

namespace Bot\Controllers;

use Bot\Controller;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;

/**
 * @RoutePrefix("/bot")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Public
 */
class IndexController extends Controller
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
			$telegram->addCommandsPath(ROOT_PATH.$this->config->application->baseDir.$this->config->application->modulesDir.'Bot/Commands/');
		
			// Here you can enable admin interface for the channel you want to manage
			$telegram->enableAdmins(['134099267']);
		
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