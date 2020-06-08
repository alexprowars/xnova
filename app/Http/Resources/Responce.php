<?php

namespace Xnova\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Xnova\Game;
use Xnova\Models\UserQuest;
use Xnova\Vars;

class Responce extends JsonResource
{
	public function toArray($request)
	{
		$route = $request->route();
		$controller = $route->getController();

		$data = [
			'error' => false,
			'host' => $request->getHttpHost(),
			'messages' => [],
			'path' => '/',
			'redirect' => '',
			'route' => [
				'controller' => $route->getName(),
				'action' => $route->getActionMethod(),
			],
			'speed' => [
				'game' => 0,
				'fleet' => 0,
				'resources' => 0,
			],
			'stats' => [
				'time' => time(),
				'timezone' => (int) date('Z'),
				'online' => (int) config('settings.users_online', 0),
				'users' => (int) config('settings.users_total', 0),
			],
			'title' => $controller->getTitle(),
			'url' => str_replace('/' . $route->getPrefix(), '', $request->getPathInfo()),
			'user' => null,
			'planet' => null,
			'view' => $controller->getViews(),
			'version' => VERSION,
			'page' => $this->resource,
		];

		$this->addData($data);

		return $data;
	}

	private function addData(&$result)
	{
		if (!Auth::check()) {
			return;
		}

		$user = Auth::user();

		$planet = $user->getCurrentPlanet();

		if ($planet) {
			$result['planet'] = $planet->toArray();
		}

		$globalMessage = config('settings.newsMessage', '');

		if ($globalMessage != '') {
			$result['messages'][] = [
				'type' => 'warning-static',
				'text' => $globalMessage
			];
		}

		if ($user->deltime > 0) {
			$result['messages'][] = [
				'type' => 'info-static',
				'text' => 'Включен режим удаления профиля!<br>Ваш аккаунт будет удалён после ' . Game::datezone("d.m.Y", $user->deltime) . ' в ' . Game::datezone("H:i:s", $user->deltime) . '. Выключить режим удаления можно в настройках игры.'
			];
		}

		if ($user->vacation > 0) {
			$result['messages'][] = [
				'type' => 'warning-static',
				'text' => 'Включен режим отпуска! Функциональность игры ограничена.'
			];
		}

		if (Session::has('_flash')) {
			$keys = Session::get('_flash')['new'] ?? [];

			foreach ($keys as $key) {
				$result['messages'][] = [
					'type' => $key,
					'text' => Session::get($key)
				];
			}
		}

		if ($user->messages_ally > 0 && $user->ally_id == 0) {
			$user->messages_ally = 0;
			$user->update();
		}

		$planetsList = Cache::remember('app::planetlist_' . $user->getId(), 600, function () use ($user) {
			return $user->getPlanets();
		});

		$planets = [];

		foreach ($planetsList as $item) {
			$planets[] = [
				'id' => (int) $item->id,
				'name' => $item->name,
				'image' => $item->image,
				'g' => (int) $item->galaxy,
				's' => (int) $item->system,
				'p' => (int) $item->planet,
				't' => (int) $item->planet_type,
				'destroy' => $item->destruyed > 0,
			];
		}

		$quests = Cache::remember('app::quests::' . $user->getId(), 3600, function () use ($user) {
			return (int) UserQuest::query()
				->where('user_id', $user->getId())
				->where('finish', 1)
				->count();
		});

		$result['user'] = [
			'id' => (int) $user->id,
			'name' => trim($user->username),
			'race' => (int) $user->race,
			'messages' => (int) $user->messages,
			'alliance' => [
				'id' => (int) $user->ally_id,
				'name' => $user->ally_name,
				'messages' => (int) $user->messages_ally
			],
			'planets' => $planets,
			'timezone' => (int) $user->getUserOption('timezone'),
			'color' => (int) $user->getUserOption('color'),
			'vacation' => $user->vacation > 0,
			'quests' => (int) $quests,
			'credits' => (int) $user->credits,
			'officiers' => [],
		];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $officier) {
			$result['user']['officiers'][] = [
				'id' => $officier,
				'time' => (int) $user->{Vars::getName($officier)}
			];
		}

		$result['speed'] = [
			'game' => Game::getSpeed('build'),
			'fleet' => Game::getSpeed('fleet'),
			'resources' => Game::getSpeed('mine')
		];
	}
}
