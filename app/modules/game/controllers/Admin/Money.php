<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Helpers;

class Money
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel < 3)
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

		$action = $controller->request->get('mode', 'string', '');

		switch ($action)
		{
			case 'add':

				if ($controller->request->getPost('username', 'string', '') != '')
				{
					$username = $controller->request->getPost('username');

					$info = $controller->db->query("SELECT id FROM game_users u WHERE ".(is_numeric($username) ? "`id` = '" . $username . "'" : "`username` = '" . $username . "'")." LIMIT 1;")->fetch();

					if (!isset($info['id']))
						$controller->message('Такого игрока не существует', 'Ошибка', '/admin/money/mode/add/', 2);

					$money = $controller->request->getPost('money', 'int', 0);

					if ($money > 0)
					{
						$controller->user->saveData(['+credits' => $money], $info['id']);

						$controller->db->insertAsDict('game_log_credits',
						[
							'uid' => $info['id'],
							'time' => time(),
							'credits' => $money,
							'type' => 6
						]);

						$controller->message('Начисление '.$money.' кредитов прошло успешно', 'Всё ок!', '/admin/money/mode/add/', 2);
					}
				}

				$controller->view->pick('admin/money_add');
				$controller->tag->setTitle('Начисление кредитов');

				break;

			case 'transactions':

				$parse = [];
				$parse['list'] = [];

				$start = $controller->request->getQuery('p', 'int', 0);
				$limit = 25;

				$elements = $controller->db->query("SELECT p.*, u.username FROM game_users_payments p LEFT JOIN game_users u ON u.id = p.user ORDER BY p.id DESC LIMIT ".$start.",".$limit."");

				while ($element = $elements->fetch())
				{
					$parse['list'][] = $element;
				}

				$total = $controller->db->fetchColumn("SELECT COUNT(*) AS num FROM game_users_payments");

				$parse['total'] = $total;
				$parse['pagination'] = Helpers::pagination($total, 25, '/admin/money/mode/transactions/', $start);

				$controller->view->pick('admin/money_transactions');
				$controller->view->setVar('parse', $parse);
				$controller->tag->setTitle("Транзакции");

				break;

			default:

				$controller->response->redirect('admin/');
		}
	}
}

?>