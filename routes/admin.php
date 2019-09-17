<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'IndexController@index')->name('admin.index');
Route::get('alliances', 'AlliancesController@index')->name('admin.alliances');
Route::get('fleets', 'FleetsController@index')->name('admin.fleets');
Route::get('manager', 'ManagerController@index')->name('admin.manager');
Route::get('manager/ip', 'ManagerController@ip')->name('admin.manager.ip');
Route::get('manager/data', 'ManagerController@data')->name('admin.manager.data');
Route::get('mailing', 'MailingController@index')->name('admin.mailing');
Route::get('messages', 'MessagesController@index')->name('admin.messages');
Route::get('server', 'ServerController@index')->name('admin.server');
Route::get('support', 'SupportController@index')->name('admin.support');
Route::get('support/detail/{id}', 'SupportController@detail')->name('admin.support.detail');
Route::get('support/send/{id}', 'SupportController@send')->name('admin.support.send');
Route::get('support/open/{id}', 'SupportController@open')->name('admin.support.open');
Route::get('support/close/{id}', 'SupportController@close')->name('admin.support.close');
Route::get('users', 'UsersController@index')->name('admin.users');
Route::get('users/add', 'UsersController@add')->name('admin.users.add');
Route::get('users/edit/{id}', 'UsersController@edit')->name('admin.users.edit');
Route::get('users/delete/{id}', 'UsersController@delete')->name('admin.users.delete');
Route::get('users/list', 'UsersController@list')->name('admin.users.list');
Route::get('users/find', 'UsersController@find')->name('admin.users.find');
Route::get('users/ban', 'UsersController@ban')->name('admin.users.ban');
Route::get('users/unban', 'UsersController@unban')->name('admin.users.unban');

Route::crud('planets', 'PlanetsController');
Route::crud('payments', 'PaymentsController');
Route::crud('contents', 'ContentsController');
Route::crud('moons', 'MoonsController');