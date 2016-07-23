<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ReplyKeyboardHide;
use Longman\TelegramBot\Request;
use Phalcon\Di;

/**
 * User "/whoami" command
 */
class AuthCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'auth';
    protected $description = 'Авторизация вашего игрового аккаунта';
    protected $usage = '/auth';
    protected $version = '1.0.1';
    protected $public = true;
    /**#@-*/

	/**
	 * Conversation Object
	 *
	 * @var \Longman\TelegramBot\Conversation
	 */
	protected $conversation;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();

        $user_id = $message->getFrom()->getId();
        $chat_id = $message->getChat()->getId();
        $text = trim($message->getText(true));

		$data = [];
		$data['chat_id'] = $chat_id;

		$this->conversation = new Conversation($user_id, $chat_id, $this->getName());

		if (!isset($this->conversation->notes['state']))
			$state = 0;
		else
			$state = (int) $this->conversation->notes['state'];

		switch ($state)
		{
			case 0:
				if (empty($text))
				{
					$this->conversation->notes['state'] = 0;
					$this->conversation->update();

					$data['text'] = 'Введите ваш логин в игре:';
					$data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);

					break;
				}

				/**
				 * @var $user \App\Models\User
				 */
				$user = User::findFirst(['conditions' => 'username = ?0', 'bind' => [$text]]);

				if (!$user)
				{
					$data['text'] = 'Такого игрока не существует в игре';

					break;
				}

				/**
				 * @var $db \App\Database
				 */
				$db = Di::getDefault()->getShared('db');

				$exist = $db->fetchOne('SELECT * FROM game_users_auth WHERE user_id = '.$user->id.' AND external_id LIKE "bot|%"');

				if ($exist)
				{
					$data['text'] = 'Этот аккаунт уже авторизован';

					$db->delete('bot_requests', 'user_id = ?', [$user->id]);
					$this->conversation->stop();

					break;
				}

				$db->delete('bot_requests', 'user_id = ?', [$user->id]);
				$db->insertAsDict('bot_requests', [
					'user_id'	=> $user->id,
					'code'		=> mt_rand(1000000, 9999999),
					'time'		=> time()
				]);

				$this->conversation->notes['login'] = $text;
				$text = '';

			case 1:

				if (empty($text))
				{
					$this->conversation->notes['state'] = 1;
					$this->conversation->update();

					$data['text'] = 'Введите код авторизации со страницы https://uni5.xnova.su/options/';

					break;
				}
				
				/**
				 * @var $user \App\Models\User
				 */
				$user = User::findFirst(['conditions' => 'username = ?0', 'bind' => [$this->conversation->notes['login']]]);
				
				if (!$user)
				{
					$data['text'] = 'Произошла ошибка. Попробуйте с самого начала';

					$this->conversation->stop();

					break;
				}

				/**
					 * @var $db \App\Database
					 */
				$db = Di::getDefault()->getShared('db');

				$request = $db->fetchOne('SELECT * FROM bot_requests WHERE user_id = '.$user->id.' AND code = '.$text.'');

				if (!$request)
				{
					$data['text'] = 'Код неверен. Введите правильный код';

					break;
				}

				$db->delete('bot_requests', 'user_id = ?', [$user->id]);
				$db->insertAsDict('game_users_auth', [
					'user_id'		=> $user->id,
					'external_id' 	=> 'bot|'.$user_id,
					'create_time' 	=> time(),
					'enter_time' 	=> time()
				]);

				$this->conversation->notes['code'] = $text;

			case 2:

				$this->conversation->update();
				$this->conversation->stop();

				$data['text'] = 'Вы успешно авторизовали бота';
		}

		return Request::sendMessage($data);
    }
}
