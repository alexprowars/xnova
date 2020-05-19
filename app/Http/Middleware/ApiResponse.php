<?php

namespace Xnova\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Xnova\Exceptions\Exception;
use Xnova\Game;
use Xnova\Controller;
use Xnova\Models\UserQuest;

class ApiResponse
{
	public function handle(Request $request, Closure $next): Response
	{
		/** @var JsonResponse $response */
		$response = $next($request);

		if ($response instanceof RedirectResponse) {
			$route = Route::current();

			$uri = rtrim(str_replace($request->root(), '', $response->getTargetUrl()), '/') . '/';
			$uri = str_replace('/' . $route->getPrefix(), '', $uri);

			return new JsonResponse([
				'success' => true,
				'data' => [
					'redirect' => $uri
				],
			]);
		}

		if (!($response instanceof JsonResponse)) {
			return $response;
		}

		$route = Route::current();
		/** @var Controller $controller */
		$controller = $route->getController();

		$data = [
			'error' => false,
			'host' => $request->getHttpHost(),
			'messages' => [],
			'path' => '/',
			'redirect' => '',
			'resources' => null,
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
			'view' => $controller->getViews(),
			'version' => VERSION,
			'page' => null,
		];

		if ($response->exception) {
			if ($response->exception instanceof Exception) {
				$data = array_merge($data, $response->getOriginalContent());
			} else {
				$code = 500;

				if ($response->exception instanceof HttpException) {
					$code = $response->exception->getStatusCode();
				}

				return new JsonResponse([
					'success' => false,
					'data' => $response->getOriginalContent(),
				], $code);
			}
		} else {
			$data['page'] = $response->getOriginalContent();
		}

		$this->afterExecuteRoute($data);

		$response->setData([
			'success' => true,
			'data' => $data,
		]);

		return $response;
	}

	private function afterExecuteRoute(&$result)
	{
		if (!Auth::check()) {
			return;
		}

		$user = Auth::user();

		$planet = $user->getCurrentPlanet();

		if ($planet) {
			$result['resources'] = $planet->getTopPanelRosources();
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
			'planet' => (int) $user->planet_current,
			'position' => false,
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
			'quests' => (int) $quests
		];

		if ($planet) {
			$result['user']['position'] = [
				'galaxy' => (int) $planet->galaxy,
				'system' => (int) $planet->system,
				'planet' => (int) $planet->planet,
				'planet_type' => (int) $planet->planet_type,
			];
		}

		$result['speed'] = [
			'game' => Game::getSpeed('build'),
			'fleet' => Game::getSpeed('fleet'),
			'resources' => Game::getSpeed('mine')
		];
	}
}
