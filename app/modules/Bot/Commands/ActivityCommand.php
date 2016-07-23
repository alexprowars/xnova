<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Helpers;
use App\Models\Fleet;
use App\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Phalcon\Di;

/**
 * User "/whoami" command
 */
class ActivityCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'activity';
    protected $description = 'Краткая сводка активности на аккаунте';
    protected $usage = '/activity';
    protected $version = '1.0.1';
    protected $public = true;
    /**#@-*/

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
		$data['text'] = '';

		/**
		 * @var $db \App\Database
		 */
		$db = Di::getDefault()->getShared('db');

		$auth = $db->fetchOne('SELECT id, user_id FROM game_users_auth WHERE external_id = "bot|'.$user_id.'"');

		if (!$auth)
		{
			$data['text'] = 'Этот аккаунт не авторизован. Для использования игровых функций необходимо пройти процедуру авторизации через команду /auth';

			return Request::sendMessage($data);
		}

		/**
		 * @var $user \App\Models\User
		 */
		$user = User::findFirst($auth['user_id']);

		if (!$user)
			$data['text'] = 'Игрок не найден';
		else
		{
			$data['text'] .= "Новых сообщений: ".$user->messages."\n";

			if ($user->ally_id > 0)
				$data['text'] .= "Новых сообщений альянса: ".$user->messages_ally."\n";

			$data['text'] .= "Летящие флоты: ";

			/**
			 * @var $fleets \App\Models\Fleet[]
			 */
			$fleets = Fleet::find(['conditions' => 'owner = :user: OR target_owner = :user:', 'bind' => ['user' => $user->id]]);

			if (!count($fleets))
				$data['text'] .= "нет\n";
			else
			{
				$data['text'] .= "\n";

				foreach ($fleets AS $fleet)
				{
					$status = false;

					if ($fleet->owner == $user->id)
					{
						if ($fleet->start_time > time())
							$status = 0;
						elseif ($fleet->end_stay > time())
							$status = 1;
						elseif (!($fleet->mission == 7 && $fleet->mess == 0))
						{
							if (($fleet->end_time > time() AND $fleet->mission != 4) OR ($fleet->mess == 1 AND $fleet->mission == 4))
								$status = 2;
						}
					}
					else
					{
						if ($fleet->start_time > time())
							$status = 0;
						elseif ($fleet->mission == 5 && $fleet->end_stay > time())
							$status = 1;
					}

					if ($status == 0)
						$time = $fleet->start_time;
					elseif ($status == 1)
						$time = $fleet->end_stay;
					else
						$time = $fleet->end_time;

					if ($status !== false)
					{
						if ($fleet->owner != $user->id)
							$data['text'] .= "Враг с ";
						else
							$data['text'] .= "С ";

						if ($status != 2)
							$data['text'] .= $fleet->owner_name." [".$fleet->splitStartPosition()."] на ".$fleet->target_owner_name." [".$fleet->splitTargetPosition()."]. Осталось времени ".Helpers::pretty_time($time - time())."\n";
						else
							$data['text'] .= $fleet->target_owner_name." [".$fleet->splitTargetPosition()."] на ".$fleet->owner_name." [".$fleet->splitStartPosition()."]. Осталось времени ".Helpers::pretty_time($time - time())."\n";
					}
				}
			}
		}

		return Request::sendMessage($data);
	}
}