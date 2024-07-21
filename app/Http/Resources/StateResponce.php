<?php

namespace App\Http\Resources;

use App\Engine\Construction;
use App\Engine\Game;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class StateResponce extends JsonResource
{
	public function toArray($request)
	{
		$user = Auth::user();
		$planet = $user?->getCurrentPlanet();

		$data = [
			'messages' => [],
			'speed' => [
				'game' => Game::getSpeed('build'),
				'fleet' => Game::getSpeed('fleet'),
				'resources' => Game::getSpeed('mine'),
			],
			'stats' => [
				'online' => (int) config('settings.usersOnline', 0),
				'users' => (int) config('settings.usersTotal', 0),
			],
			'user' => $user ? User::make($user) : null,
			'planet' => $planet ? Planet::make($planet) : null,
			'queue' => Construction::showBuildingQueue($user, $planet),
			'data' => $this->resource,
			'version' => VERSION,
		];

		if ($user) {
			$globalMessage = config('settings.newsMessage', '');

			if (!empty($globalMessage)) {
				$data['messages'][] = [
					'type' => 'warning-static',
					'text' => $globalMessage
				];
			}

			if ($user->delete_time) {
				$data['messages'][] = [
					'type' => 'info-static',
					'text' => 'Включен режим удаления профиля!<br>Ваш аккаунт будет удалён после ' . Game::datezone("d.m.Y", $user->delete_time) . ' в ' . Game::datezone("H:i:s", $user->delete_time) . '. Выключить режим удаления можно в настройках игры.'
				];
			}

			if ($user->isVacation()) {
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
