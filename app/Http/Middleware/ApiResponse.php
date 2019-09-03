<?php

namespace Xnova\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\Exception;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Exceptions\SuccessException;
use Xnova\Game;
use Xnova\Controller;
use Xnova\Models\UsersQuest;
use Xnova\User;

class ApiResponse
{
	public function handle (Request $request, Closure $next): Response
	{
		/** @var JsonResponse $response */
		$response = $next($request);

		if ($response instanceof RedirectResponse)
		{
			return new JsonResponse([
				'success' => true,
				'data' => [
					'redirect' => rtrim(str_replace($request->root(), '', $response->getTargetUrl()), '/').'/'
				],
			]);
		}

		if (!($response instanceof JsonResponse))
			return $response;

		$original = $response->getOriginalContent();

		$route = Route::current();
		/** @var Controller $controller */
		$controller = $route->getController();

		$data = [
			'chat' => null,
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
				'online' => (int) Config::get('game.users_online', 0),
				'users' => (int) Config::get('game.users_total', 0),
			],
			'title' => $controller->getTitle(),
			'url' => str_replace('/'.$route->getPrefix(), '', $request->getPathInfo()),
			'user' => null,
			'view' => $controller->getViews(),
			'version' => VERSION,
		];

		$data = array_merge($data, $this->afterExecuteRoute());

		if ($response->exception)
		{
			if ($response->exception instanceof RedirectException)
				$data['redirect'] = $original['redirect'];
			elseif ($response->exception instanceof PageException)
				$data['error'] = $original;
			elseif ($response->exception instanceof Exception)
			{
				$type = 'notice';

				if ($response->exception instanceof ErrorException)
					$type = 'error';
				elseif ($response->exception instanceof SuccessException)
					$type = 'success';

				$data['messages'][] = [
					'type' => $type,
					'text' => $response->exception->getMessage(),
				];
			}
			elseif ($response->exception instanceof \Exception)
			{
				$data['messages'][] = [
					'type' => 'error',
					'text' => $response->exception->getMessage(),
				];
			}

			$original = null;
		}

		$data['page'] = $original ?? [];

		$response->setData([
			'success' => true,
			'data' => $data,
		]);

		return $response;
	}

	public function afterExecuteRoute ()
	{
		if (!Auth::check())
			return [];

		/** @var User $user */
		$user = Auth::user();

		$result = [
			'resources' => $user->getCurrentPlanet()->getTopPanelRosources(),
		];

		$messages = [];

		$globalMessage = Config::get('game.newsMessage', '');

		if ($globalMessage != '')
		{
			$messages[] = [
				'type' => 'warning-static',
				'text' => $globalMessage
			];
		}

		if ($user->deltime > 0)
		{
			$messages[] = [
				'type' => 'info-static',
				'text' => 'Включен режим удаления профиля!<br>Ваш аккаунт будет удалён после '.Game::datezone("d.m.Y", $user->deltime).' в '.Game::datezone("H:i:s", $user->deltime).'. Выключить режим удаления можно в настройках игры.'
			];
		}

		if ($user->vacation > 0)
		{
			$messages[] = [
				'type' => 'warning-static',
				'text' => 'Включен режим отпуска! Функциональность игры ограничена.'
			];
		}

		if (Session::has('_flash'))
		{
			$keys = Session::get('_flash')['new'] ?? [];

			foreach ($keys as $key)
			{
				$messages[] = [
					'type' => $key,
					'text' => Session::get($key)
				];
			}
		}

		$result['messages'] = $messages;

		if ($user->messages_ally > 0 && $user->ally_id == 0)
		{
			$user->messages_ally = 0;
			$user->update();
		}

		$planetsList = Cache::get('app::planetlist_'.$user->getId());

		if (!$planetsList)
		{
			$planetsList = $user->getPlanets();

			if (count($planetsList))
				Cache::put('app::planetlist_'.$user->getId(), $planetsList, 600);
		}

		$planets = [];

		foreach ($planetsList as $item)
		{
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

		$quests = Cache::get('app::quests::'.$user->getId());

		if ($quests === null)
		{
			$quests = (int) UsersQuest::get()->where('user_id', $user->getId())->where('finish', 1)->count();

			Cache::put('app::quests::'.$user->getId(), $quests, 3600);
		}

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

		$planet = $user->getCurrentPlanet();

		if ($planet)
		{
			$result['user']['position'] = [
				'galaxy' => (int) $planet->galaxy,
				'system' => (int) $planet->system,
				'planet' => (int) $planet->planet,
				'planet_type' => (int) $planet->planet_type,
			];
		}

		$result['chat'] = [
			'key' => md5($user->getId().'|'.$user->username.Config::get('chat.key')),
			'server' => Config::get('chat.host').':'.Config::get('chat.port'),
		];

		$result['speed'] = [
			'game' => Game::getSpeed('build'),
			'fleet' => Game::getSpeed('fleet'),
			'resources' => Game::getSpeed('mine')
		];

		return $result;
	}
}