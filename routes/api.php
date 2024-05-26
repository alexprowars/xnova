<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('state', [Controllers\StateController::class, 'index'])->name('state');
Route::get('banned', [Controllers\BannedController::class, 'index'])->name('banned');
Route::get('contacts', [Controllers\ContactsController::class, 'index'])->name('contacts');
Route::get('content/{slug}', [Controllers\ContentController::class, 'index'])->name('content');
Route::match(['get', 'post'], 'registration', [Controllers\IndexController::class, 'registration'])->name('registration');
Route::get('log/{id}', [Controllers\LogController::class, 'info'])->name('log.info');
Route::get('news', [Controllers\NewsController::class, 'index'])->name('news');
Route::match(['get', 'post'], 'payment/robokassa', [Controllers\PaymentController::class, 'robokassa'])->name('payment.robokassa');
Route::match(['get', 'post'], 'stat', [Controllers\StatController::class, 'index'])->name('stat');
Route::match(['get', 'post'], 'xnsim', [Controllers\XnsimController::class, 'index'])->name('xnsim');
Route::get('players/{id}', [Controllers\PlayersController::class, 'index'])->name('players');

Route::post('login', [Controllers\LoginController::class, 'byCredentials'])->name('login');
Route::get('login/social/{service}', [Controllers\LoginController::class, 'byServices'])->name('login.socials');
Route::get('login/callback/{service}', [Controllers\LoginController::class, 'servicesCallback'])->name('login.callback');
Route::post('login/reset', [Controllers\LoginController::class, 'resetPassword'])->name('login.reset');

Route::middleware(['auth'])->group(function () {
	Route::get('tech', [Controllers\TechController::class, 'index'])->name('tech');
	Route::get('tech/{id}', [Controllers\TechController::class, 'info'])->name('tech.detail');
	Route::post('start', [Controllers\StartController::class, 'save'])->name('start');
	Route::match(['get', 'post'], 'sim', [Controllers\SimController::class, 'index'])->name('sim');
	Route::get('records', [Controllers\RecordsController::class, 'index'])->name('records');
	Route::get('players/stat/{id}', [Controllers\PlayersController::class, 'stat'])->name('players.stat');
	Route::get('logout', [Controllers\LogoutController::class, 'index'])->name('logout');
	Route::get('info/{id}', [Controllers\InfoController::class, 'index'])->name('info');
	Route::get('hall', [Controllers\HallController::class, 'index'])->name('hall');
	Route::post('chat/send', [Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
	Route::get('chat/last', [Controllers\ChatController::class, 'last'])->name('chat.last');

	Route::match(['get', 'post'], 'alliance', [Controllers\AllianceController::class, 'index'])->name('alliance');
	Route::match(['get', 'post'], 'alliance/search', [Controllers\AllianceController::class, 'search'])->name('alliance.search');
	Route::post('alliance/make', [Controllers\AllianceController::class, 'make'])->name('alliance.make');
	Route::match(['get', 'post'], 'alliance/apply', [Controllers\AllianceController::class, 'apply'])->name('alliance.apply');
	Route::match(['get', 'post'], 'alliance/chat', [Controllers\AllianceController::class, 'chat'])->name('alliance.chat');
	Route::match(['get', 'post'], 'alliance/admin', [Controllers\AllianceController::class, 'admin'])->name('alliance.admin');
	Route::match(['get', 'post'], 'alliance/admin/rights', [Controllers\AllianceController::class, 'adminRights'])->name('alliance.admin.rights');
	Route::match(['get', 'post'], 'alliance/admin/requests', [Controllers\AllianceController::class, 'adminRequests'])->name('alliance.admin.requests');
	Route::match(['get', 'post'], 'alliance/admin/name', [Controllers\AllianceController::class, 'adminName'])->name('alliance.admin.name');
	Route::match(['get', 'post'], 'alliance/admin/tag', [Controllers\AllianceController::class, 'adminTag'])->name('alliance.admin.tag');
	Route::match(['get', 'post'], 'alliance/admin/exit', [Controllers\AllianceController::class, 'adminExit'])->name('alliance.admin.exit');
	Route::match(['get', 'post'], 'alliance/admin/give', [Controllers\AllianceController::class, 'adminGive'])->name('alliance.admin.give');
	Route::match(['get', 'post'], 'alliance/admin/members', [Controllers\AllianceController::class, 'adminMembers'])->name('alliance.admin.members');
	Route::match(['get', 'post'], 'alliance/diplomacy', [Controllers\AllianceController::class, 'diplomacy'])->name('alliance.diplomacy');
	Route::match(['get', 'post'], 'alliance/exit', [Controllers\AllianceController::class, 'exit'])->name('alliance.exit');
	Route::match(['get', 'post'], 'alliance/members', [Controllers\AllianceController::class, 'members'])->name('alliance.members');
	Route::get('alliance/info/{id}', [Controllers\AllianceController::class, 'info'])->name('alliance.info');
	Route::get('alliance/stat/{id}', [Controllers\AllianceController::class, 'stat'])->name('alliance.stat');

	Route::match(['get', 'post'], 'buddy', [Controllers\BuddyController::class, 'index'])->name('buddy');
	Route::match(['get', 'post'], 'buddy/requests', [Controllers\BuddyController::class, 'requests'])->name('buddy.requests');
	Route::match(['get', 'post'], 'buddy/new', [Controllers\BuddyController::class, 'new'])->name('buddy.new');
	Route::match(['get', 'post'], 'buddy/delete', [Controllers\BuddyController::class, 'delete'])->name('buddy.delete');
	Route::match(['get', 'post'], 'buildings', [Controllers\BuildingsController::class, 'index'])->name('buildings');
	Route::match(['get', 'post'], 'credits', [Controllers\CreditsController::class, 'index'])->name('credits');
	Route::match(['get', 'post'], 'defense', [Controllers\DefenseController::class, 'index'])->name('defense');
	Route::get('fleet', [Controllers\FleetController::class, 'index'])->name('fleet');
	Route::get('fleet/g{galaxy}/s{system}/p{planet}/t{type}/m{mission}', [Controllers\FleetController::class, 'index'])->name('fleet.galaxy');
	Route::match(['get', 'post'], 'fleet/checkout', [Controllers\Fleet\FleetController::class, 'index'])->name('fleet.checkout');
	Route::match(['get', 'post'], 'fleet/send', [Controllers\Fleet\FleetSendController::class, 'index'])->name('fleet.send');
	Route::match(['get', 'post'], 'fleet/back', [Controllers\Fleet\FleetBackController::class, 'index'])->name('fleet.back');
	Route::match(['get', 'post'], 'fleet/shortcut', [Controllers\Fleet\FleetShortcutController::class, 'index'])->name('fleet.shortcut');
	Route::match(['get', 'post'], 'fleet/shortcut/add', [Controllers\Fleet\FleetShortcutController::class, 'add'])->name('fleet.shortcut.add');
	Route::match(['get', 'post'], 'fleet/shortcut/{id}', [Controllers\Fleet\FleetShortcutController::class, 'view'])->name('fleet.shortcut.view');
	Route::match(['get', 'post'], 'fleet/verband/{id}', [Controllers\Fleet\FleetVerbandController::class, 'index'])->name('fleet.verband');
	Route::match(['get', 'post'], 'fleet/quick', [Controllers\Fleet\FleetQuickController::class, 'index'])->name('fleet.quick');
	Route::match(['get', 'post'], 'galaxy', [Controllers\GalaxyController::class, 'index'])->name('galaxy');
	Route::get('imperium', [Controllers\ImperiumController::class, 'index'])->name('imperium');
	Route::match(['get', 'post'], 'log', [Controllers\LogController::class, 'index'])->name('log');
	Route::post('log/new', [Controllers\LogController::class, 'new'])->name('log.new');
	Route::match(['get', 'post'], 'merchant', [Controllers\MerchantController::class, 'index'])->name('merchant');
	Route::match(['get', 'post'], 'messages', [Controllers\MessagesController::class, 'index'])->name('messages');
	Route::match(['get', 'post'], 'messages/write/{id}', [Controllers\MessagesController::class, 'write'])->name('messages.send');
	Route::match(['get', 'post'], 'notes', [Controllers\NotesController::class, 'index'])->name('notes');
	Route::match(['get', 'post'], 'notes/edit/{id}', [Controllers\NotesController::class, 'edit'])->name('notes.edit');
	Route::post('notes/new', [Controllers\NotesController::class, 'new'])->name('notes.new');
	Route::get('officier', [Controllers\OfficierController::class, 'index'])->name('officier');
	Route::post('officier/buy', [Controllers\OfficierController::class, 'buy'])->name('officier.buy');
	Route::get('options', [Controllers\OptionsController::class, 'index'])->name('options');
	Route::post('options/save', [Controllers\OptionsController::class, 'save'])->name('options.save');
	Route::match(['get', 'post'], 'overview', [Controllers\OverviewController::class, 'index'])->name('overview');
	Route::match(['get', 'post'], 'overview/rename', [Controllers\OverviewController::class, 'rename'])->name('overview.rename');
	Route::match(['get', 'post'], 'overview/delete', [Controllers\OverviewController::class, 'delete'])->name('overview.delete');
	Route::get('phalanx', [Controllers\PhalanxController::class, 'index'])->name('phalanx');
	Route::match(['get', 'post'], 'race', [Controllers\RaceController::class, 'index'])->name('race');
	Route::get('refers', [Controllers\RefersController::class, 'index'])->name('refers');
	Route::match(['get', 'post'], 'research', [Controllers\ResearchController::class, 'index'])->name('research');
	Route::match(['get', 'post'], 'resources', [Controllers\ResourcesController::class, 'index'])->name('resources');
	Route::get('rocket', [Controllers\RocketController::class, 'index'])->name('rocket');
	Route::get('rw/{id}/{key}', [Controllers\RwController::class, 'index'])->name('rw');
	Route::match(['get', 'post'], 'search', [Controllers\SearchController::class, 'index'])->name('search');
	Route::match(['get', 'post'], 'shipyard', [Controllers\ShipyardController::class, 'index'])->name('shipyard');
	Route::get('support', [Controllers\SupportController::class, 'index'])->name('support');
	Route::get('support/info/{id}', [Controllers\SupportController::class, 'info'])->name('support.info');
	Route::post('support/answer/{id}', [Controllers\SupportController::class, 'answer'])->name('support.answer');
	Route::post('support/add', [Controllers\SupportController::class, 'add'])->name('support.add');
	Route::get('tutorial', [Controllers\TutorialController::class, 'index'])->name('tutorial');
	Route::match(['get', 'post'], 'tutorial/{stage}', [Controllers\TutorialController::class, 'info'])->name('tutorial.info');
});
