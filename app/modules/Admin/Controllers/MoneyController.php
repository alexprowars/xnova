<?php

namespace Admin\Controllers;

use Admin\Controller;
use Xnova\Helpers;
use Xnova\Models\User;

/**
 * @RoutePrefix("/admin/money")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class MoneyController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 3)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'money',
			'title' => 'Финансы',
			'icon'	=> 'rub',
			'sort'	=> 40,
			'childrens' => [
				[
					'code'	=> 'add',
					'title' => 'Начислить кредиты',
				],
				[
					'code'	=> 'index',
					'title' => 'Транзакции',
				]
			]
		]];
	}

	public function indexAction ()
	{
		$parse = [];
		$parse['list'] = [];

		$start = $this->request->getQuery('p', 'int', 0);
		$limit = 25;

		$elements = $this->db->query("SELECT p.*, u.username FROM game_users_payments p LEFT JOIN game_users u ON u.id = p.user ORDER BY p.id DESC LIMIT ".$start.",".$limit."");

		while ($element = $elements->fetch())
		{
			$parse['list'][] = $element;
		}

		$total = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_users_payments");

		$parse['total'] = $total;
		$parse['pagination'] = Helpers::pagination($total, 25, '/admin/money/mode/transactions/', $start);

		$this->view->setVar('parse', $parse);
		$this->tag->setTitle("Транзакции");
	}

	public function addAction ()
	{
		if ($this->request->getPost('username', 'string', '') != '')
		{
			$username = $this->request->getPost('username');

			$info = $this->db->query("SELECT id FROM game_users u WHERE ".(is_numeric($username) ? "`id` = '" . $username . "'" : "`username` = '" . $username . "'")." LIMIT 1;")->fetch();

			if (!isset($info['id']))
				$this->message('Такого игрока не существует', 'Ошибка', '/admin/money/mode/add/', 2);

			$money = $this->request->getPost('money', 'int', 0);

			if ($money > 0)
			{
				$this->user->saveData(['+credits' => $money], $info['id']);

				$this->db->insertAsDict('game_log_credits',
				[
					'uid' => $info['id'],
					'time' => time(),
					'credits' => $money,
					'type' => 6
				]);

				User::sendMessage($info['id'], 0, 0, 1, 'Обработка платежей', 'На ваш счет зачислено ' . $money . ' кредитов');

				$this->message('Начисление '.$money.' кредитов прошло успешно', 'Всё ок!', '/admin/money/mode/add/', 2);
			}
		}

		$this->tag->setTitle('Начисление кредитов');
	}
}