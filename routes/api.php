<?php

use Illuminate\Support\Facades\Route;

Route::get('banned', 'BannedController@index')->name('banned');
Route::match(['get', 'post'],'alliance', 'AllianceController@index')->name('alliance');
Route::match(['get', 'post'],'buddy', 'BuddyController@index')->name('buddy');
Route::match(['get', 'post'],'buildings', 'BuildingsController@index')->name('buildings');
Route::get('chat', 'ChatController@index')->name('chat');
Route::get('contacts', 'ContactsController@index')->name('contacts');
Route::get('content', 'ContentController@index')->name('content');
Route::match(['get', 'post'],'credits', 'CreditsController@index')->name('credits');
Route::match(['get', 'post'],'defense', 'DefenseController@index')->name('defense');
Route::get('error', 'ErrorController@index')->name('error');

Route::get('fleet', 'FleetController@index')->name('fleet');
Route::get('fleet/g{galaxy}/s{system}/p{planet}/t{type}/m{mission}', 'FleetController@index')->name('fleet');
Route::match(['get', 'post'],'fleet/one', 'Fleet\FleetStageOneController@index')->name('fleet.one');
Route::match(['get', 'post'],'fleet/two', 'Fleet\FleetStageTwoController@index')->name('fleet.two');
Route::match(['get', 'post'],'fleet/three', 'Fleet\FleetStageThreeController@index')->name('fleet.three');
Route::match(['get', 'post'],'fleet/back', 'Fleet\FleetBackController@index')->name('fleet.back');
Route::match(['get', 'post'],'fleet/shortcut', 'Fleet\FleetShortcutController@index')->name('fleet.shortcut');
Route::match(['get', 'post'],'fleet/verband/{id}', 'Fleet\FleetVerbandController@index')->name('fleet.verband');
Route::match(['get', 'post'],'fleet/quick', 'Fleet\FleetQuickController@index')->name('fleet.quick');

Route::match(['get', 'post'],'galaxy', 'GalaxyController@index')->name('galaxy');
Route::get('hall', 'HallController@index')->name('hall');
Route::get('imperium', 'ImperiumController@index')->name('imperium');
Route::get('/', 'IndexController@index')->name('index');
Route::match(['get', 'post'],'registration', 'IndexController@registration')->name('registration');
Route::match(['get', 'post'],'remind', 'IndexController@remind')->name('remind');
Route::match(['get', 'post'],'login', 'IndexController@login')->name('login');
Route::get('info', 'InfoController@index')->name('info');
Route::match(['get', 'post'],'log', 'LogController@index')->name('log');
Route::get('logout', 'LogoutController@index')->name('logout');
Route::match(['get', 'post'],'merchant', 'MerchantController@index')->name('merchant');
Route::match(['get', 'post'],'messages', 'MessagesController@index')->name('messages');
Route::match(['get', 'post'],'messages/write/{id}', 'MessagesController@write')->name('messages');
Route::get('news', 'NewsController@index')->name('news');
Route::match(['get', 'post'],'notes', 'NotesController@index')->name('notes');
Route::match(['get', 'post'],'officier', 'OfficierController@index')->name('officier');
Route::match(['get', 'post'],'options', 'OptionsController@index')->name('options');

Route::match(['get', 'post'],'overview', 'OverviewController@index')->name('overview');
Route::match(['get', 'post'],'overview/rename', 'OverviewController@rename')->name('overview.rename');
Route::match(['get', 'post'],'overview/delete', 'OverviewController@delete')->name('overview.delete');

Route::get('payment', 'PaymentController@index')->name('payment');
Route::match(['get', 'post'],'payment/robokassa', 'PaymentController@robokassa')->name('payment.robokassa');
Route::get('phalanx', 'PhalanxController@index')->name('phalanx');
Route::get('players/{id}', 'PlayersController@index')->name('players');
Route::get('players/stat/{id}', 'PlayersController@stat')->name('players');
Route::match(['get', 'post'],'race', 'RaceController@index')->name('race');
Route::get('records', 'RecordsController@index')->name('records');
Route::get('refers', 'RefersController@index')->name('refers');
Route::match(['get', 'post'],'research', 'ResearchController@index')->name('research');
Route::match(['get', 'post'],'resources', 'ResourcesController@index')->name('resources');
Route::get('rocket', 'RocketController@index')->name('rocket');
Route::get('rw/{id}/{key}', 'RwController@index')->name('rw');
Route::match(['get', 'post'],'search', 'SearchController@index')->name('search');
Route::match(['get', 'post'],'shipyard', 'ShipyardController@index')->name('shipyard');
Route::match(['get', 'post'],'sim', 'SimController@index')->name('sim');
Route::match(['get', 'post'],'start', 'StartController@index')->name('start');

Route::match(['get', 'post'], 'stat', 'StatController@index')->name('stat');

Route::get('support', 'SupportController@index')->name('support');
Route::get('support/info/{id}', 'SupportController@info')->name('support.info');
Route::post('support/answer/{id}', 'SupportController@answer')->name('support.answer');
Route::post('support/add', 'SupportController@add')->name('support.add');

Route::get('tech', 'TechController@index')->name('tech');
Route::get('tech/{id}', 'TechController@info')->name('tech');

Route::get('tutorial', 'TutorialController@index')->name('tutorial');
Route::match(['get', 'post'],'tutorial/{stage}', 'TutorialController@info')->name('tutorial.info');

Route::match(['get', 'post'],'xnsim', 'XnsimController@index')->name('xnsim');