<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;
use Xnova\Helpers;
use Xnova\User;

class MoneyController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'money',
			'title' => 'Финансы',
			'icon'	=> 'money',
			'sort'	=> 40,
			'childrens' => [[
				'code'	=> 'add',
				'title' => 'Начислить кредиты',
			], [
				'code'	=> 'index',
				'title' => 'Транзакции',
			]]
		]];
	}

	public function index ()
	{
		$parse = [];
		$parse['list'] = [];

		$start = $this->request->getQuery('p', 'int', 0);
		$limit = 25;

		$elements = $this->db->query("SELECT p.*, u.username FROM users_payments p LEFT JOIN users u ON u.id = p.user ORDER BY p.id DESC LIMIT ".$start.",".$limit."");

		while ($element = $elements->fetch())
		{
			$parse['list'][] = $element;
		}

		$total = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM users_payments");

		$parse['total'] = $total;
		$parse['pagination'] = Helpers::pagination($total, 25, '/admin/money/', $start);

		View::share('title', "Транзакции");

		return view('admin.money.index', ['parse' => $parse]);
	}

	public function add ()
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		if ($this->request->getPost('username', 'string', '') != '')
		{
			$username = $this->request->getPost('username');

			$info = $this->db->query("SELECT id FROM users u WHERE ".(is_numeric($username) ? "`id` = '" . $username . "'" : "`username` = '" . $username . "'")." LIMIT 1;")->fetch();

			if (!isset($info['id']))
				$this->message('Такого игрока не существует', 'Ошибка', '/admin/money/add/', 2);

			$money = $this->request->getPost('money', 'int', 0);

			if ($money > 0)
			{
				$this->user->saveData(['+credits' => $money], $info['id']);

				$this->db->insertAsDict('log_credits',
				[
					'uid' => $info['id'],
					'time' => time(),
					'credits' => $money,
					'type' => 6
				]);

				User::sendMessage($info['id'], 0, 0, 1, 'Обработка платежей', 'На ваш счет зачислено ' . $money . ' кредитов');

				$this->message('Начисление '.$money.' кредитов прошло успешно', 'Всё ок!', '/admin/money/add/', 2);
			}
		}

		View::share('title', 'Начисление кредитов');

		return view('admin.money.add', []);
	}
}