<?php

namespace App\Http\Controllers;

use App\Controller;
use App\Game;
use App\Http\Resources\Planet;
use App\Http\Resources\User;
use Illuminate\Support\Facades\Auth;

class StateController extends Controller
{
	public function index()
	{
		$planet = null;

		if (Auth::check()) {
			$planet = Auth::user()->getCurrentPlanet();
		}

		$data = [
			'messages' => [],
			'speed' => [
				'game' => Game::getSpeed('build'),
				'fleet' => Game::getSpeed('fleet'),
				'resources' => Game::getSpeed('mine'),
			],
			'stats' => [
				'time' => time(),
				'timezone' => (int) date('Z'),
				'online' => (int) config('settings.usersOnline', 0),
				'users' => (int) config('settings.usersTotal', 0),
			],
			'user' => Auth::check() ? User::make(Auth::user()) : null,
			'planet' => $planet ? Planet::make($planet) : null,
			'version' => VERSION,
		];

		if (Auth::check()) {
			$user = Auth::user();

			$globalMessage = config('settings.newsMessage', '');

			if (!empty($globalMessage)) {
				$data['messages'][] = [
					'type' => 'warning-static',
					'text' => $globalMessage
				];
			}

			if ($user->deltime > 0) {
				$data['messages'][] = [
					'type' => 'info-static',
					'text' => 'Включен режим удаления профиля!<br>Ваш аккаунт будет удалён после ' . Game::datezone("d.m.Y", $user->deltime) . ' в ' . Game::datezone("H:i:s", $user->deltime) . '. Выключить режим удаления можно в настройках игры.'
				];
			}

			if ($user->vacation > 0) {
				$data['messages'][] = [
					'type' => 'warning-static',
					'text' => 'Включен режим отпуска! Функциональность игры ограничена.'
				];
			}

			if (session()->has('_flash')) {
				$keys = session('_flash')['new'] ?? [];

				foreach ($keys as $key) {
					$data['messages'][] = [
						'type' => $key,
						'text' => session($key)
					];
				}
			}

			if ($user->messages_ally > 0 && $user->alliance_id == 0) {
				$user->messages_ally = 0;
				$user->update();
			}
		}

		return $data;
	}
}
