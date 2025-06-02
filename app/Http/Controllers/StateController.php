<?php

namespace App\Http\Controllers;

use App\Engine\Game;
use App\Engine\Locale;
use App\Http\Resources\PlanetResource;
use App\Http\Resources\QueueResource;
use App\Http\Resources\UserResource;
use App\Settings;
use Illuminate\Support\Facades\Auth;

class StateController extends Controller
{
	public function index(Settings $settings)
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
			'settings' => [
				'language' => Locale::getPreferredLocale(),
				'merchant' => Game::getMerchantExchangeRate(),
			],
			'stats' => [
				'online' => $settings->usersOnline ?: 0,
				'users' => $settings->usersTotal ?: 0,
			],
			'user' => null,
			'version' => VERSION,
		];

		if ($user) {
			$data['user'] = UserResource::make($user);

			if ($planet) {
				$data['planet'] = PlanetResource::make($planet);
				$data['queue'] = QueueResource::make($user);
			}

			$globalMessage = $settings->globalMessage ?: '';

			if (!empty($globalMessage)) {
				$data['messages'][] = [
					'type' => 'warning-static',
					'text' => $globalMessage
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

			if ($user->messages_ally > 0 && !$user->alliance_id) {
				$user->messages_ally = 0;
				$user->update();
			}
		}

		return $data;
	}
}
