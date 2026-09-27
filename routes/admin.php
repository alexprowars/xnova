<?php

use App\Http\Controllers\Admin;
use App\Http\Middleware\AdminAccess;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
	->name('admin.')
	->middleware(['auth', AdminAccess::class])
	->group(function () {
		Route::get('/', Admin\DashboardController::class)->name('dashboard');
		Route::post('locale', [Admin\DashboardController::class, 'locale'])->name('locale');
		Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings');
		Route::post('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
		Route::get('users/{user}/logs', [Admin\UserController::class, 'logs'])
			->whereNumber('user')
			->name('users.logs');
		Route::post('users/{user}/actions', [Admin\UserController::class, 'action'])
			->whereNumber('user')
			->name('users.action');
		Route::post('users/{user}/research/{entity}', [Admin\UserController::class, 'research'])
			->whereNumber(['user', 'entity'])
			->name('users.research');
		Route::resource('users', Admin\UserController::class)->except(['show', 'destroy', 'update']);
		Route::post('users/{user}', [Admin\UserController::class, 'update'])
			->whereNumber('user')
			->name('users.update');
		Route::resource('roles', Admin\RoleController::class)->except(['show', 'destroy', 'update']);
		Route::post('roles/{role}', [Admin\RoleController::class, 'update'])
			->whereNumber('role')
			->name('roles.update');
		Route::resource('planets', Admin\PlanetController::class)->except(['show', 'destroy', 'update']);
		Route::post('planets/{planet}', [Admin\PlanetController::class, 'update'])
			->whereNumber('planet')
			->name('planets.update');
		Route::post('planets/{planet}/delete', [Admin\PlanetController::class, 'destroy'])
			->whereNumber('planet')
			->name('planets.destroy');
		Route::post('planets/{planet}/buildings/{entity}', [Admin\PlanetController::class, 'building'])
			->whereNumber(['planet', 'entity'])
			->name('planets.building');
		Route::post('planets/{planet}/queue/{queue}', [Admin\PlanetController::class, 'queue'])
			->whereNumber(['planet', 'queue'])
			->name('planets.queue');
		Route::resource('moons', Admin\MoonController::class)
			->parameters(['moons' => 'planet'])
			->except(['show', 'destroy', 'update']);
		Route::post('moons/{planet}', [Admin\MoonController::class, 'update'])
			->whereNumber('planet')
			->name('moons.update');
		Route::post('moons/{planet}/delete', [Admin\MoonController::class, 'destroy'])
			->whereNumber('planet')
			->name('moons.destroy');
		Route::post('moons/{planet}/buildings/{entity}', [Admin\MoonController::class, 'building'])
			->whereNumber(['planet', 'entity'])
			->name('moons.building');
		Route::post('moons/{planet}/queue/{queue}', [Admin\MoonController::class, 'queue'])
			->whereNumber(['planet', 'queue'])
			->name('moons.queue');
		Route::get('fleets', [Admin\FleetController::class, 'index'])->name('fleets.index');
		Route::post('fleets/{fleet}/actions', [Admin\FleetController::class, 'action'])
			->whereNumber('fleet')
			->name('fleets.action');
		Route::resource('alliances', Admin\AllianceController::class)->except(['show', 'destroy', 'update']);
		Route::post('alliances/{alliance}', [Admin\AllianceController::class, 'update'])
			->whereNumber('alliance')
			->name('alliances.update');
		Route::post('alliances/{alliance}/members/{member}', [Admin\AllianceController::class, 'member'])
			->whereNumber(['alliance', 'member'])
			->name('alliances.member');
		Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
		Route::get('messages/mailing', [Admin\MessageController::class, 'mailing'])->name('messages.mailing');
		Route::post('messages/mailing', [Admin\MessageController::class, 'sendMailing'])->name(
			'messages.sendMailing',
		);
		Route::get('messages/{message}', [Admin\MessageController::class, 'show'])
			->whereNumber('message')
			->name('messages.show');
		Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])
			->whereNumber('message')
			->name('messages.destroy');
		Route::resource('ai', Admin\AiController::class)->except(['show', 'destroy', 'update']);
		Route::post('ai/{ai}', [Admin\AiController::class, 'update'])
			->whereNumber('ai')
			->name('ai.update');
		Route::resource('content', Admin\ContentController::class)->except(['show', 'destroy', 'update']);
		Route::post('content/{content}', [Admin\ContentController::class, 'update'])
			->whereNumber('content')
			->name('content.update');
	});
