<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Xnova\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ReplyKeyboardHide;
use Longman\TelegramBot\Request;
use Phalcon\Di;

/**
 * User "/whoami" command
 */
class MessageCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'message';
    protected $description = 'Отправка сообщения игроку';
    protected $usage = '/message или /message <логин>';
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

		/**
		 * @var $db \Xnova\Database
		 */
		$db = Di::getDefault()->getShared('db');

		$auth = $db->fetchOne('SELECT a.*, u.username FROM game_users_auth a, game_users u WHERE u.id = a.user_id AND a.external_id = "bot|'.$user_id.'"');

		if (!$auth)
		{
			$data['text'] = 'Этот аккаунт не авторизован. Для использования игровых функций необходимо пройти процедуру авторизации через команду /auth';

			return Request::sendMessage($data);
		}

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

					$data['text'] = 'Введите логин игрока';
					$data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);

					break;
				}

				$user = User::findFirst(['columns' => 'id, username', 'conditions' => 'username = ?0', 'bind' => [$text]]);

				if (!$user)
				{
					$data['text'] = 'Такого игрока не существует в игре';

					break;
				}

				$this->conversation->notes['login'] = $text;
				$text = '';

			case 1:

				if (empty($text))
				{
					$this->conversation->notes['state'] = 1;
					$this->conversation->update();

					$data['text'] = 'Введите текст сообщения';

					break;
				}

				$this->conversation->notes['text'] = $text;

				$user = User::findFirst(['columns' => 'id', 'conditions' => 'username = ?0', 'bind' => [$this->conversation->notes['login']]]);

				if (!$user)
				{
					$data['text'] = 'Произошла ошибка. Попробуйте с самого начала';

					$this->conversation->stop();

					break;
				}

				if (!User::sendMessage($user->id, $auth['user_id'], time(), 1, $auth['username'].' (Отправлено из Telegram)', $text))
				{
					$data['text'] = 'Произошла ошибка при отправке сообщения';

					$this->conversation->stop();

					break;
				}

			case 2:
				$this->conversation->update();
				$this->conversation->stop();

				$data['text'] = 'Ваше сообщение отправлено';
		}

		return Request::sendMessage($data);
	}
}