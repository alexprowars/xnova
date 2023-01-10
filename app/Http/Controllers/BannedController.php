<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Controller;

class BannedController extends Controller
{
	public function index()
	{
		$query = DB::select('SELECT u.username AS user_1, u2.username AS user_2, b.* FROM banned b LEFT JOIN users u ON u.id = b.who LEFT JOIN users u2 ON u2.id = b.author ORDER BY b.id DESC');

		$items = [];

		foreach ($query as $u) {
			$items[] = [
				'user' => [
					'id' => (int) $u['who'],
					'name' => $u['user_1']
				],
				'moderator' => [
					'id' => (int) $u['author'],
					'name' => $u['user_2']
				],
				'time' => (int) $u['time'],
				'time_end' => (int) $u['longer'],
				'reason' => $u['theme'],
			];
		}

		$this->showTopPanel(false);
		$this->setTitle('Список заблокированных игроков');

		return [
			'items' => $items
		];
	}
}
