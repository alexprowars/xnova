<?php

namespace App\Http\Controllers;

use App\Engine\Entity\Model\PlanetEntity;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Format;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Models;
use App\Models\Planet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class OptionsController extends Controller
{
	public function email(ChangeEmailRequest $request)
	{
		$this->user->email = $request->input('email');
		$this->user->email_verified_at = null;
		$this->user->save();
	}

	public function save(Request $request)
	{
		if ($this->user->vacation?->isPast() && $request->has('vacation') && !((int) $request->post('vacation', 0))) {
			$this->user->vacation = null;
		}

		if ($request->has('locale')) {
			$locale = $request->post('locale');

			if (!in_array($locale, ['en', 'ru'])) {
				$locale = 'en';
			}

			$this->user->locale = $locale;
		}

		if ($request->has('delete')) {
			$delete = (int) $request->post('delete', 0);

			if ($delete && !$this->user->delete_time) {
				$this->user->delete_time = now()->addDays(7);
			}

			if (!$delete && $this->user->delete_time) {
				$this->user->delete_time = null;
			}
		}

		if (!$this->user->vacation) {
			if (!empty($request->post('name')) && $request->post('name') != $this->user->username) {
				$username = preg_replace('/([\s\x{0}\x{0B}]+)/iu', ' ', $request->post('name'));

				if ($this->user->username_change?->greaterThan(now()->subDay())) {
					throw new Exception('Смена игрового имени возможна лишь раз в сутки.');
				}

				$existName = Models\User::query()->where('username', $username)->exists();

				if ($existName) {
					throw new Exception('Дaннoe имя aккayнтa yжe иcпoльзyeтcя в игpe');
				}

				if (!preg_match('/^[a-zA-Za-яA-Я0-9_.,\-!?* ]+$/u', $username) || Str::length($username) < 5) {
					throw new Exception('Дaннoe имя aккayнтa cлишкoм кopoткoe или имeeт зaпpeщeнныe cимвoлы');
				}

				$this->user->username = $username;
				$this->user->username_change = now();
				$this->user->update();
			}

			if ($request->has('sex')) {
				$this->user->sex = ($request->post('sex', 'M') == 'F') ? 2 : 1;
			}

			if ($request->has('color')) {
				$color = $request->post('color', 1);
				$color = (int) max(1, min(13, $color));

				$this->user->setOption('color', $color);
			}

			if ($request->has('timezone')) {
				$timezone = (int) $request->post('timezone', 0);

				if ($timezone < -12 || $timezone > 12) {
					$timezone = null;
				}

				$this->user->setOption('timezone', $timezone);
			}

			if ($request->has('spy')) {
				$spy = (int) $request->post('spy', 1);

				if ($spy < 1 || $spy > 1000) {
					$spy = 1;
				}

				$this->user->setOption('spy', $spy);
			}

			if ($request->has('about')) {
				$this->user->about = Format::text($request->post('text', ''));
			}

			if ($request->has('records')) {
				$this->user->setOption('records', !empty($request->post('records')));
			}

			if ($request->has('bbcode')) {
				$this->user->setOption('bb_parser', !empty($request->post('bbcode')));
			}

			if ($request->has('chatbox')) {
				$this->user->setOption('chatbox', !empty($request->post('chatbox')));
			}

			if ($request->has('available')) {
				$this->user->setOption('only_available', !empty($request->post('available')));
			}

			if ($request->has('planetlist')) {
				$this->user->setOption('planetlist', $request->post('planetlist'));
			}

			if ($request->has('planetlistselect')) {
				$this->user->setOption('planetlistselect', $request->post('planetlistselect'));
			}

			if ($request->has('settings_sort')) {
				$this->user->setOption('planet_sort', (int) $request->post('settings_sort', 0));
			}

			if ($request->has('settings_order')) {
				$this->user->setOption('planet_sort_order', (int) $request->post('settings_order', 0));
			}

			if ($request->hasFile('photo')) {
				$file = $request->file('photo');

				if ($file->isValid()) {
					$validator = Validator::make(
						['file' => $file],
						['photo' => 'image,mimetypes:image/jpg,image/webp,image/png']
					);

					if ($validator->passes()) {
						$this->user->clearMediaCollection();

						try {
							$this->user->addMedia($file)->toMediaCollection();
						} catch (Throwable $e) {
							Log::error($e);
						}
					}
				}
			}

			if ($request->post('photo_delete')) {
				$this->user->clearMediaCollection();
			}
		}

		$this->user->update();

		cache()->forget('app::planetlist_' . $this->user->id);
	}

	public function password(ChangePasswordRequest $request)
	{
		$this->user->password = Hash::make($request->input('password'));
		$this->user->save();

		Auth::logout();
	}

	public function vacation()
	{
		if ($this->user->isVacation()) {
			return;
		}

		$queueCount = $this->user->queue_count;

		$flyingFleets = Models\Fleet::query()
			->whereBelongsTo($this->user)
			->count();

		if ($queueCount > 0) {
			throw new Exception('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe. Строится: ' . $queueCount . ' объектов.');
		} elseif ($flyingFleets > 0) {
			throw new Exception('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.');
		}

		$vacationTime = now()->addDays(config('game.vacationModeTime', 2));

		$buildsId = [4, 12, 212];

		foreach (Vars::getResources() as $res) {
			$buildsId[] = Vars::getIdByName($res . '_mine');
		}

		$this->user->planets->each(function (Planet $planet) use ($buildsId) {
			$planet->entities->whereIn('id', $buildsId)->each(fn(PlanetEntity $entity) => $entity->factor = 0);
			$planet->save();
		});

		$this->user->vacation = $vacationTime;
		$this->user->save();
	}
}
