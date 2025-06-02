<?php

use App\Http\Controllers;
use App\Http\Middleware\IsVacationMode;
use Illuminate\Support\Facades\Route;

Route::get('state', [Controllers\StateController::class, 'index']);
Route::get('blocked', [Controllers\BlockedController::class, 'index']);
Route::get('contacts', [Controllers\ContactsController::class, 'index']);
Route::get('content/{slug}', [Controllers\ContentController::class, 'index']);
Route::post('registration', [Controllers\RegistrationController::class, 'index']);
Route::get('logs/{id}', [Controllers\LogsController::class, 'info'])->whereNumber('id');
Route::match(['get', 'post'], 'stat', [Controllers\StatController::class, 'index']);
Route::match(['get', 'post'], 'stat/alliances', [Controllers\StatController::class, 'alliances']);
Route::match(['get', 'post'], 'stat/races', [Controllers\StatController::class, 'races']);
Route::match(['get', 'post'], 'xnsim', [Controllers\XnsimController::class, 'index']);
Route::get('players/{id}', [Controllers\PlayersController::class, 'index'])->whereNumber('id');

Route::post('login', [Controllers\LoginController::class, 'credentials']);
Route::get('login/social/{service}', [Controllers\LoginController::class, 'services']);
Route::get('login/callback/{service}', [Controllers\LoginController::class, 'callback']);
Route::get('login/reset', [Controllers\ResetPasswordController::class, 'reset']);
Route::post('login/reset', [Controllers\ResetPasswordController::class, 'send']);

Route::middleware(['auth'])->group(function () {
	Route::get('tech', [Controllers\TechController::class, 'index']);
	Route::get('tech/{id}', [Controllers\TechController::class, 'info'])->whereNumber('id');

	Route::post('start', [Controllers\StartController::class, 'save']);
	Route::post('start/race', [Controllers\StartController::class, 'race']);

	Route::match(['get', 'post'], 'sim', [Controllers\SimController::class, 'index']);
	Route::get('records', [Controllers\RecordsController::class, 'index']);
	Route::get('players/stat/{id}', [Controllers\PlayersController::class, 'stat']);
	Route::post('logout', [Controllers\LogoutController::class, 'index']);

	Route::get('info/{id}', [Controllers\InfoController::class, 'index'])->whereNumber('id');
	Route::post('info/{id}/missiles', [Controllers\InfoController::class, 'missiles'])->whereNumber('id');
	Route::post('info/{id}/alliance', [Controllers\InfoController::class, 'alliance'])->whereNumber('id');

	Route::match(['get', 'post'], 'hall', [Controllers\HallController::class, 'index']);

	Route::post('chat', [Controllers\ChatController::class, 'send']);
	Route::get('chat/last', [Controllers\ChatController::class, 'last']);

	Route::get('alliance', [Controllers\Alliance\AllianceController::class, 'index']);
	Route::post('alliance/search', [Controllers\Alliance\AllianceController::class, 'search']);
	Route::post('alliance/create', [Controllers\Alliance\AllianceController::class, 'create']);
	Route::get('alliance/join/{id}', [Controllers\Alliance\AllianceController::class, 'join']);
	Route::post('alliance/join/{id}', [Controllers\Alliance\AllianceController::class, 'joinSend']);
	Route::get('alliance/chat', [Controllers\Alliance\AllianceChatController::class, 'index']);
	Route::post('alliance/chat', [Controllers\Alliance\AllianceChatController::class, 'send']);
	Route::delete('alliance/chat', [Controllers\Alliance\AllianceChatController::class, 'delete']);
	Route::get('alliance/admin', [Controllers\Alliance\AllianceAdminController::class, 'index']);
	Route::post('alliance/admin', [Controllers\Alliance\AllianceAdminController::class, 'update']);
	Route::post('alliance/admin/text', [Controllers\Alliance\AllianceAdminController::class, 'text']);
	Route::post('alliance/admin/name', [Controllers\Alliance\AllianceAdminController::class, 'name']);
	Route::post('alliance/admin/tag', [Controllers\Alliance\AllianceAdminController::class, 'tag']);
	Route::post('alliance/admin/remove', [Controllers\Alliance\AllianceAdminController::class, 'remove']);
	Route::get('alliance/admin/give', [Controllers\Alliance\AllianceAdminController::class, 'give']);
	Route::post('alliance/admin/give', [Controllers\Alliance\AllianceAdminController::class, 'giveSend']);
	Route::get('alliance/diplomacy', [Controllers\Alliance\AllianceDiplomacyController::class, 'index']);
	Route::post('alliance/diplomacy/create', [Controllers\Alliance\AllianceDiplomacyController::class, 'create']);
	Route::post('alliance/diplomacy/accept', [Controllers\Alliance\AllianceDiplomacyController::class, 'accept']);
	Route::post('alliance/diplomacy/reject', [Controllers\Alliance\AllianceDiplomacyController::class, 'reject']);
	Route::post('alliance/exit', [Controllers\Alliance\AllianceController::class, 'exit']);
	Route::get('alliance/info/{id}', [Controllers\Alliance\AllianceController::class, 'info'])->whereNumber('id');
	Route::get('alliance/stat/{id}', [Controllers\Alliance\AllianceController::class, 'stat'])->whereNumber('id');
	Route::get('alliance/members', [Controllers\Alliance\AllianceMembersController::class, 'index']);
	Route::get('alliance/admin/members', [Controllers\Alliance\AllianceMembersController::class, 'index']);
	Route::post('alliance/admin/members/kick', [Controllers\Alliance\AllianceMembersController::class, 'kick']);
	Route::post('alliance/admin/members/rank', [Controllers\Alliance\AllianceMembersController::class, 'rank']);
	Route::delete('alliance/request/{id}', [Controllers\Alliance\AllianceRequestsController::class, 'remove']);
	Route::get('alliance/admin/requests', [Controllers\Alliance\AllianceRequestsController::class, 'index']);
	Route::post('alliance/admin/requests/accept', [Controllers\Alliance\AllianceRequestsController::class, 'accept']);
	Route::post('alliance/admin/requests/reject', [Controllers\Alliance\AllianceRequestsController::class, 'reject']);
	Route::get('alliance/admin/ranks', [Controllers\Alliance\AllianceRanksController::class, 'index']);
	Route::post('alliance/admin/ranks', [Controllers\Alliance\AllianceRanksController::class, 'update']);
	Route::post('alliance/admin/ranks/create', [Controllers\Alliance\AllianceRanksController::class, 'create']);
	Route::delete('alliance/admin/ranks/{id}', [Controllers\Alliance\AllianceRanksController::class, 'remove']);

	Route::get('friends', [Controllers\FriendsController::class, 'index']);
	Route::get('friends/requests', [Controllers\FriendsController::class, 'requests']);
	Route::get('friends/new/{id}', [Controllers\FriendsController::class, 'new']);
	Route::post('friends/new/{id}', [Controllers\FriendsController::class, 'create']);
	Route::delete('friends/{id}', [Controllers\FriendsController::class, 'delete'])->whereNumber('id');
	Route::post('friends/{id}/approve', [Controllers\FriendsController::class, 'approve'])->whereNumber('id');

	Route::get('buildings', [Controllers\BuildingsController::class, 'index'])->middleware(IsVacationMode::class);
	Route::post('buildings/build/{action}', [Controllers\BuildingsController::class, 'build'])->middleware(IsVacationMode::class)->whereIn('action', ['insert', 'destroy']);
	Route::post('buildings/queue/{action}', [Controllers\BuildingsController::class, 'queue'])->middleware(IsVacationMode::class)->whereIn('action', ['cancel', 'remove']);

	Route::post('credits/pay', [Controllers\CreditsController::class, 'pay']);

	Route::get('defense', [Controllers\DefenseController::class, 'index'])->middleware(IsVacationMode::class);
	Route::post('defense/queue', [Controllers\DefenseController::class, 'queue'])->middleware(IsVacationMode::class);

	Route::get('fleet', [Controllers\Fleet\FleetController::class, 'index']);
	Route::get('fleet/list', [Controllers\Fleet\FleetController::class, 'list']);
	Route::get('fleet/g{galaxy}/s{system}/p{planet}/t{type}/m{mission}', [Controllers\Fleet\FleetController::class, 'index']);
	Route::post('fleet/checkout', [Controllers\Fleet\FleetCheckoutController::class, 'index'])->middleware(IsVacationMode::class);
	Route::post('fleet/send', [Controllers\Fleet\FleetSendController::class, 'index'])->middleware(IsVacationMode::class);
	Route::post('fleet/back', [Controllers\Fleet\FleetBackController::class, 'index']);
	Route::get('fleet/shortcut', [Controllers\Fleet\FleetShortcutController::class, 'index']);
	Route::get('fleet/shortcut/create', [Controllers\Fleet\FleetShortcutController::class, 'create']);
	Route::post('fleet/shortcut', [Controllers\Fleet\FleetShortcutController::class, 'store']);
	Route::get('fleet/shortcut/{id}', [Controllers\Fleet\FleetShortcutController::class, 'view'])->whereNumber('id');
	Route::post('fleet/shortcut/{id}', [Controllers\Fleet\FleetShortcutController::class, 'update'])->whereNumber('id');
	Route::delete('fleet/shortcut/{id}', [Controllers\Fleet\FleetShortcutController::class, 'delete'])->whereNumber('id');
	Route::get('fleet/verband/{id}', [Controllers\Fleet\FleetVerbandController::class, 'index'])->whereNumber('id');
	Route::post('fleet/verband/{id}', [Controllers\Fleet\FleetVerbandController::class, 'create'])->whereNumber('id');
	Route::post('fleet/verband/{id}/name', [Controllers\Fleet\FleetVerbandController::class, 'name'])->whereNumber('id');
	Route::post('fleet/verband/{id}/user', [Controllers\Fleet\FleetVerbandController::class, 'user'])->whereNumber('id');
	Route::post('fleet/quick', [Controllers\Fleet\FleetQuickController::class, 'index'])->middleware(IsVacationMode::class);

	Route::get('galaxy', [Controllers\GalaxyController::class, 'index']);
	Route::get('empire', [Controllers\EmpireController::class, 'index']);

	Route::get('logs', [Controllers\LogsController::class, 'index']);
	Route::delete('logs/{id}', [Controllers\LogsController::class, 'delete']);
	Route::post('logs', [Controllers\LogsController::class, 'create']);

	Route::post('merchant/exchange', [Controllers\MerchantController::class, 'exchange']);

	Route::get('messages', [Controllers\MessagesController::class, 'index']);
	Route::get('messages/write/{id}', [Controllers\MessagesController::class, 'write']);
	Route::post('messages/write/{id}', [Controllers\MessagesController::class, 'send']);
	Route::post('messages/{id}/abuse', [Controllers\MessagesController::class, 'abuse']);
	Route::post('messages/delete', [Controllers\MessagesController::class, 'delete']);

	Route::get('notes', [Controllers\NotesController::class, 'index']);
	Route::delete('notes', [Controllers\NotesController::class, 'delete']);
	Route::get('notes/{id}', [Controllers\NotesController::class, 'edit'])->whereNumber('id');
	Route::post('notes/{id}', [Controllers\NotesController::class, 'update'])->whereNumber('id');
	Route::post('notes/create', [Controllers\NotesController::class, 'create']);

	Route::get('officiers', [Controllers\OfficiersController::class, 'index']);
	Route::post('officiers/buy', [Controllers\OfficiersController::class, 'buy'])->middleware(IsVacationMode::class);

	Route::get('options', [Controllers\OptionsController::class, 'index']);
	Route::post('options', [Controllers\OptionsController::class, 'save']);
	Route::post('options/email', [Controllers\OptionsController::class, 'email']);
	Route::post('options/password', [Controllers\OptionsController::class, 'password']);

	Route::get('phalanx', [Controllers\PhalanxController::class, 'index'])->middleware(IsVacationMode::class);
	Route::get('race', [Controllers\RaceController::class, 'index']);
	Route::post('race/change', [Controllers\RaceController::class, 'change']);
	Route::get('referrals', [Controllers\ReferralsController::class, 'index']);

	Route::get('research', [Controllers\ResearchController::class, 'index'])->middleware(IsVacationMode::class);
	Route::post('research/{action}', [Controllers\ResearchController::class, 'action'])->middleware(IsVacationMode::class)->whereIn('action', ['cancel', 'search']);

	Route::get('resources', [Controllers\ResourcesController::class, 'index']);
	Route::post('resources/buy', [Controllers\ResourcesController::class, 'buy'])->middleware(IsVacationMode::class);
	Route::post('resources/shutdown', [Controllers\ResourcesController::class, 'shutdown'])->middleware(IsVacationMode::class);
	Route::post('resources/state', [Controllers\ResourcesController::class, 'state'])->middleware(IsVacationMode::class);

	Route::post('rocket', [Controllers\RocketController::class, 'index'])->middleware(IsVacationMode::class);
	Route::get('rw/{id}', [Controllers\RwController::class, 'index'])->name('log.view')->whereNumber('id')->middleware('signed:relative');
	Route::post('search', [Controllers\SearchController::class, 'index']);

	Route::get('shipyard', [Controllers\ShipyardController::class, 'index'])->middleware(IsVacationMode::class);
	Route::post('shipyard/queue', [Controllers\ShipyardController::class, 'queue'])->middleware(IsVacationMode::class);

	Route::get('support', [Controllers\SupportController::class, 'index']);
	Route::get('support/info/{id}', [Controllers\SupportController::class, 'info'])->whereNumber('id');
	Route::post('support/answer/{id}', [Controllers\SupportController::class, 'answer'])->whereNumber('id');
	Route::post('support/add', [Controllers\SupportController::class, 'add']);

	Route::get('tutorial', [Controllers\TutorialController::class, 'index']);
	Route::get('tutorial/{id}', [Controllers\TutorialController::class, 'info'])->whereNumber('id');
	Route::post('tutorial/{id}', [Controllers\TutorialController::class, 'finish'])->whereNumber('id');

	Route::post('user/planet/{id}', [Controllers\UserController::class, 'setPlanet']);
	Route::post('user/daily', [Controllers\UserController::class, 'dailyBonus']);

	Route::delete('planet/delete', [Controllers\PlanetController::class, 'delete']);
	Route::post('planet/rename', [Controllers\PlanetController::class, 'rename']);
	Route::post('planet/image', [Controllers\PlanetController::class, 'image']);
});
