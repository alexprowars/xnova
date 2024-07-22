<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('state', [Controllers\StateController::class, 'index']);
Route::get('banned', [Controllers\BannedController::class, 'index']);
Route::get('contacts', [Controllers\ContactsController::class, 'index']);
Route::get('content/{slug}', [Controllers\ContentController::class, 'index']);
Route::post('registration', [Controllers\RegistrationController::class, 'index']);
Route::get('log/{id}', [Controllers\LogController::class, 'info'])->whereNumber('id');
Route::get('news', [Controllers\NewsController::class, 'index']);
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

	Route::post('chat/send', [Controllers\ChatController::class, 'sendMessage']);
	Route::get('chat/last', [Controllers\ChatController::class, 'last']);

	Route::match(['get', 'post'], 'alliance', [Controllers\AllianceController::class, 'index']);
	Route::match(['get', 'post'], 'alliance/search', [Controllers\AllianceController::class, 'search']);
	Route::post('alliance/make', [Controllers\AllianceController::class, 'make']);
	Route::match(['get', 'post'], 'alliance/apply', [Controllers\AllianceController::class, 'apply']);
	Route::get('alliance/chat', [Controllers\AllianceChatController::class, 'index']);
	Route::post('alliance/chat', [Controllers\AllianceChatController::class, 'send']);
	Route::delete('alliance/chat', [Controllers\AllianceChatController::class, 'delete']);
	Route::match(['get', 'post'], 'alliance/admin', [Controllers\AllianceAdminController::class, 'index']);
	Route::match(['get', 'post'], 'alliance/admin/rights', [Controllers\AllianceAdminController::class, 'rights']);
	Route::match(['get', 'post'], 'alliance/admin/requests', [Controllers\AllianceAdminController::class, 'requests']);
	Route::post('alliance/admin/name', [Controllers\AllianceAdminController::class, 'name']);
	Route::post('alliance/admin/tag', [Controllers\AllianceAdminController::class, 'tag']);
	Route::post('alliance/admin/remove', [Controllers\AllianceAdminController::class, 'remove']);
	Route::match(['get', 'post'], 'alliance/admin/give', [Controllers\AllianceAdminController::class, 'give']);
	Route::match(['get', 'post'], 'alliance/admin/members', [Controllers\AllianceAdminController::class, 'members']);
	Route::match(['get', 'post'], 'alliance/diplomacy', [Controllers\AllianceController::class, 'diplomacy']);
	Route::match(['get', 'post'], 'alliance/exit', [Controllers\AllianceController::class, 'exit']);
	Route::match(['get', 'post'], 'alliance/members', [Controllers\AllianceController::class, 'members']);
	Route::get('alliance/info/{id}', [Controllers\AllianceController::class, 'info'])->whereNumber('id');
	Route::get('alliance/stat/{id}', [Controllers\AllianceController::class, 'stat'])->whereNumber('id');

	Route::match(['get', 'post'], 'buddy', [Controllers\BuddyController::class, 'index']);
	Route::match(['get', 'post'], 'buddy/requests', [Controllers\BuddyController::class, 'requests']);
	Route::match(['get', 'post'], 'buddy/new/{id}', [Controllers\BuddyController::class, 'new']);

	Route::post('buddy/delete/{id}', [Controllers\BuddyController::class, 'delete'])->whereNumber('id');
	Route::post('buddy/approve/{id}', [Controllers\BuddyController::class, 'approve'])->whereNumber('id');

	Route::get('buildings', [Controllers\BuildingsController::class, 'index']);
	Route::post('buildings/build/{action}', [Controllers\BuildingsController::class, 'build'])->whereIn('action', ['insert', 'destroy']);
	Route::post('buildings/queue/{action}', [Controllers\BuildingsController::class, 'queue'])->whereIn('action', ['cancel', 'remove']);

	Route::match(['get', 'post'], 'credits', [Controllers\CreditsController::class, 'index']);

	Route::get('defense', [Controllers\DefenseController::class, 'index']);
	Route::post('defense/queue', [Controllers\DefenseController::class, 'queue']);

	Route::get('fleet', [Controllers\FleetController::class, 'index']);
	Route::get('fleet/g{galaxy}/s{system}/p{planet}/t{type}/m{mission}', [Controllers\FleetController::class, 'index']);
	Route::match(['get', 'post'], 'fleet/checkout', [Controllers\Fleet\FleetCheckoutController::class, 'index']);
	Route::match(['get', 'post'], 'fleet/send', [Controllers\Fleet\FleetSendController::class, 'index']);
	Route::post('fleet/back', [Controllers\Fleet\FleetBackController::class, 'index']);
	Route::get('fleet/shortcut', [Controllers\Fleet\FleetShortcutController::class, 'index']);
	Route::get('fleet/shortcut/create', [Controllers\Fleet\FleetShortcutController::class, 'create']);
	Route::post('fleet/shortcut', [Controllers\Fleet\FleetShortcutController::class, 'store']);
	Route::get('fleet/shortcut/{id}', [Controllers\Fleet\FleetShortcutController::class, 'view'])->whereNumber('id');
	Route::post('fleet/shortcut/{id}', [Controllers\Fleet\FleetShortcutController::class, 'update'])->whereNumber('id');
	Route::delete('fleet/shortcut/{id}', [Controllers\Fleet\FleetShortcutController::class, 'delete'])->whereNumber('id');
	Route::match(['get', 'post'], 'fleet/verband/{id}', [Controllers\Fleet\FleetVerbandController::class, 'index'])->whereNumber('id');
	Route::post('fleet/quick', [Controllers\Fleet\FleetQuickController::class, 'index']);

	Route::match(['get', 'post'], 'galaxy', [Controllers\GalaxyController::class, 'index']);
	Route::get('imperium', [Controllers\ImperiumController::class, 'index']);

	Route::match(['get', 'post'], 'log', [Controllers\LogController::class, 'index']);
	Route::post('log/new', [Controllers\LogController::class, 'new']);

	Route::get('merchant', [Controllers\MerchantController::class, 'index']);
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

	Route::get('officier', [Controllers\OfficierController::class, 'index']);
	Route::post('officier/buy', [Controllers\OfficierController::class, 'buy']);

	Route::get('options', [Controllers\OptionsController::class, 'index']);
	Route::post('options', [Controllers\OptionsController::class, 'save']);
	Route::post('options/email', [Controllers\OptionsController::class, 'email']);

	Route::get('overview', [Controllers\OverviewController::class, 'index']);
	Route::post('overview/daily', [Controllers\OverviewController::class, 'daily']);
	Route::get('overview/rename', [Controllers\OverviewController::class, 'rename']);
	Route::post('overview/rename', [Controllers\OverviewController::class, 'renameAction']);
	Route::post('overview/image', [Controllers\OverviewController::class, 'image']);
	Route::delete('overview/delete/{id}', [Controllers\OverviewController::class, 'delete'])->whereNumber('id');

	Route::get('phalanx', [Controllers\PhalanxController::class, 'index']);
	Route::match(['get', 'post'], 'race', [Controllers\RaceController::class, 'index']);
	Route::get('refers', [Controllers\RefersController::class, 'index']);

	Route::get('research', [Controllers\ResearchController::class, 'index']);
	Route::post('research/{action}', [Controllers\ResearchController::class, 'action'])->whereIn('action', ['cancel', 'search']);

	Route::get('resources', [Controllers\ResourcesController::class, 'index']);
	Route::post('resources/buy', [Controllers\ResourcesController::class, 'buy']);
	Route::post('resources/shutdown', [Controllers\ResourcesController::class, 'shutdown']);
	Route::post('resources/state', [Controllers\ResourcesController::class, 'state']);

	Route::post('rocket', [Controllers\RocketController::class, 'index']);
	Route::get('rw/{id}/{key}', [Controllers\RwController::class, 'index'])->whereNumber('id');
	Route::post('search', [Controllers\SearchController::class, 'index']);

	Route::get('shipyard', [Controllers\ShipyardController::class, 'index']);
	Route::post('shipyard/queue', [Controllers\ShipyardController::class, 'queue']);

	Route::get('support', [Controllers\SupportController::class, 'index']);
	Route::get('support/info/{id}', [Controllers\SupportController::class, 'info'])->whereNumber('id');
	Route::post('support/answer/{id}', [Controllers\SupportController::class, 'answer'])->whereNumber('id');
	Route::post('support/add', [Controllers\SupportController::class, 'add']);

	Route::get('tutorial', [Controllers\TutorialController::class, 'index']);
	Route::get('tutorial/{id}', [Controllers\TutorialController::class, 'info'])->whereNumber('id');
	Route::post('tutorial/{id}', [Controllers\TutorialController::class, 'finish'])->whereNumber('id');
});
