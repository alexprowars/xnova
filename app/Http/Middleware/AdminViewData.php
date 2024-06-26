<?php

namespace App\Http\Middleware;

use App\Engine\Vars;
use Closure;
use DirectoryIterator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AdminViewData
{
	public function handle(Request $request, Closure $next): Response
	{
		Vars::init();

		$user = Auth::user();

		$route = Route::current();

		View::share('route_controller', str_replace('admin.', '', $route->getName()));
		View::share('route_action', $route->getActionMethod());
		View::share('user', $user);

		$menu = Cache::remember('ADMIN_SIDEBAR_MENU_' . $user->id, 3600, function () {
			return $this->generateMenu();
		});

		View::share('main_menu', $menu);

		return $next($request);
	}

	private function generateMenu()
	{
		$user = Auth::user();

		$menu = [];

		$files = new DirectoryIterator(app_path('Http/Controllers/Admin'));

		foreach ($files as $file) {
			if (!$file->isFile() || !str_contains($file->getFilename(), 'Controller.php')) {
				continue;
			}

			$class = 'App\Http\Controllers\Admin\\' . str_replace('.php', '', $file->getFilename());

			if (method_exists($class, 'getMenu')) {
				$items = $class::getMenu();

				if (!isset($items[0])) {
					$items = [$items];
				}

				foreach ($items as $item) {
					if (empty($item['title'])) {
						continue;
					}

					if (!$user->can('controller ' . $item['code'])) {
						continue;
					}

					if (!isset($item['icon'])) {
						$item['icon'] = '';
					}

					if (!isset($item['childrens']) || !is_array($item['childrens'])) {
						$item['childrens'] = [];
					}

					if (!isset($item['sort'])) {
						$item['sort'] = '';
					}

					$url = $item['url'] ?? null;

					if ($item['code'] && !$url) {
						$url = URL::route('admin.' . $item['code'], [], false);
					}

					foreach ($item['childrens'] as $i => $child) {
						$item['childrens'][$i]['url'] = $item['url'] ?? null;

						if ($child['code']) {
							if ($child['code'] === 'index') {
								$item['childrens'][$i]['url'] = $url;
							} else {
								$item['childrens'][$i]['url'] = $child['url'] ?? URL::route('admin.' . $item['code'] . '.' . $child['code'], [], false);
							}
						}
					}

					$menu[] = [
						'code'		=> $item['code'],
						'title' 	=> $item['title'],
						'icon' 		=> $item['icon'],
						'sort' 		=> $item['sort'],
						'url' 		=> $url,
						'childrens'	=> $item['childrens']
					];
				}
			}
		}

		uasort($menu, function ($a, $b) {
			return ($a['sort'] > $b['sort'] ? 1 : -1);
		});

		return $menu;
	}
}
