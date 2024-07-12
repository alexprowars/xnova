<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix(config('backpack.base.route_prefix', 'admin'))
	->middleware((array) config('backpack.base.middleware_key', 'admin'))
	->name('admin.')
	->group(function () {
		Route::get('alliances', [Admin\AlliancesController::class, 'index'])->name('alliances');
		Route::get('manager', [Admin\ManagerController::class, 'index'])->name('manager');
		Route::get('manager/ip', [Admin\ManagerController::class, 'ip'])->name('manager.ip');
		Route::get('manager/data', [Admin\ManagerController::class, 'data'])->name('manager.data');
		Route::match(['get', 'post'], 'mailing', [Admin\MailingController::class, 'index'])->name('mailing');
		Route::get('support', [Admin\SupportController::class, 'index'])->name('support');
		Route::get('support/detail/{id}', [Admin\SupportController::class, 'detail'])->name('support.detail');
		Route::get('support/send/{id}', [Admin\SupportController::class, 'send'])->name('support.send');
		Route::get('support/open/{id}', [Admin\SupportController::class, 'open'])->name('support.open');
		Route::get('support/close/{id}', [Admin\SupportController::class, 'close'])->name('support.close');
		Route::match(['get', 'post'], 'users/ban', [Admin\UsersController::class, 'ban'])->name('users.ban');
		Route::match(['get', 'post'], 'users/unban', [Admin\UsersController::class, 'unban'])->name('users.unban');

		Route::crud('users', Admin\UsersController::class);
	});
