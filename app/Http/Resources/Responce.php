<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Game;

class Responce extends JsonResource
{
	public function toArray($request)
	{
		$route = $request->route();
		$controller = $route->getController();

		$planet = null;

		if (Auth::check()) {
			$planet = Auth::user()->getCurrentPlanet();
		}

		$data = [
			'error' => false,
			'messages' => [],
			'route' => [
				'controller' => $route->getName(),
				'action' => $route->getActionMethod(),
			],
			'speed' => [
				'game' => Game::getSpeed('build'),
				'fleet' => Game::getSpeed('fleet'),
				'resources' => Game::getSpeed('mine'),
			],
			'stats' => [
				'time' => time(),
				'timezone' => (int) date('Z'),
				'online' => (int) config('settings.users_online', 0),
				'users' => (int) config('settings.users_total', 0),
			],
			'user' => $this->when(Auth::check(), User::make(Auth::user())),
			'planet' => $this->when($planet !== null, Planet::make($planet)),
			'page' => $this->resource,
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

			if ($user->messages_ally > 0 && $user->ally_id == 0) {
				$user->messages_ally = 0;
				$user->update();
			}
		}

		return $data;
	}
}
