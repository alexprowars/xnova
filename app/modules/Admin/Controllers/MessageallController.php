<?php

namespace Admin\Controllers;

use Admin\Controller;
use Xnova\Models\User;

/**
 * @RoutePrefix("/admin/messageall")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class MessageallController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 1)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'messageall',
			'title' => 'Рассылка',
			'icon'	=> 'bullhorn',
			'sort'	=> 180
		]];
	}

	public function indexAction ()
	{
		if ($this->request->hasPost("tresc"))
		{
			$kolor = '';

			if ($this->user->authlevel == 3)
				$kolor = 'yellow';
			elseif ($this->user->authlevel == 1)
				$kolor = 'skyblue';
			elseif ($this->user->authlevel == 2)
				$kolor = 'yellow';

			if ((isset($_POST["tresc"]) && $_POST["tresc"] != '') && (isset($_POST["temat"]) && $_POST["temat"] != ''))
			{
				$sq = $this->db->query("SELECT `id` FROM game_users");

				$Time = time();

				$From 		= "<font color=\"" . $kolor . "\">Информационное сообщение (".$this->user->username.")</font>";
				$Message 	= $_POST['tresc'];

				while ($u = $sq->fetch())
					User::sendMessage($u['id'], false, $Time, 1, $From, $Message);

				$this->message("<font color=\"lime\">Сообщение успешно отправлено всем игрокам!</font>", "Выполнено", "/admin/messageall/", 3);
			}
			else
				$this->message("<font color=\"red\">Не все поля заполнены!</font>", "Ошибка", "/admin/messageall/", 3);
		}

		$this->tag->setTitle('Рассылка');
	}
}