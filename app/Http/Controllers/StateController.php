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
	public function index(Settings $settings): array
	{
		$user = Auth::user();

		$data = [
			'messages' => [],
			'speed' => [
				'game' => Game::getSpeed('build'),
				'fleet' => Game::getSpeed('fleet'),
				'resources' => Game::getSpeed('mine'),
			],
			'locale' => Locale::getPreferredLocale(),
			'stats' => [
				'online' => $settings->usersOnline ?: 0,
				'users' => $settings->usersTotal ?: 0,
			],
			'user' => null,
			'version' => config('game.version'),
		];

		if ($user) {
			$data['user'] = UserResource::make($user);

			if ($planet = $user->getCurrentPlanet()) {
				$data['planet'] = PlanetResource::make($planet);
				$data['queue'] = QueueResource::make($user);
			}

			$globalMessage = $settings->globalMessage ?: '';

			if (!empty($globalMessage)) {
				$data['messages'][] = [
					'type' => 'warning',
					'text' => $globalMessage
				];
			}

			if ($user->messages_ally > 0 && !$user->alliance_id) {
				$user->messages_ally = 0;
				$user->update();
			}
		}

		return $data;
	}
}
