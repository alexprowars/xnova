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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
		if (!empty($request->post('name')) && $request->post('name') != $this->user->username) {
			$username = preg_replace("/([\s\x{0}\x{0B}]+)/iu", " ", $request->post('name'));

			if ($this->user->username_change?->greaterThan(now()->subDay())) {
				throw new Exception('Смена игрового имени возможна лишь раз в сутки.');
			}

			$existName = Models\User::query()->where('username', $username)->exists();

			if ($existName) {
				throw new Exception('Дaннoe имя aккayнтa yжe иcпoльзyeтcя в игpe');
			}

			if (!preg_match("/^[a-zA-Za-яA-Я0-9_.,\-!?* ]+$/u", $username) || mb_strlen($username) < 5) {
				throw new Exception('Дaннoe имя aккayнтa cлишкoм кopoткoe или имeeт зaпpeщeнныe cимвoлы');
			}

			$this->user->username = $username;
			$this->user->username_change = now();
			$this->user->update();
		}

		if ($this->user->vacation?->isFuture()) {
			$vacation = $this->user->vacation;

			if (empty($request->post('vacation'))) {
				$vacation = null;
			}
		} else {
			$vacation = null;

			if ($request->post('vacation')) {
				$queueCount = $this->user->queue_count;

				$userFlyingFleets = Models\Fleet::query()->whereBelongsTo($this->user)->count();

				if ($queueCount > 0) {
					throw new Exception('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe. Строится: ' . $queueCount . ' объектов.');
				} elseif ($userFlyingFleets > 0) {
					throw new Exception('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.');
				}

				if (!$this->user->vacation) {
					$vacation = now()->addDays(config('game.vacationModeTime', 2));
				} else {
					$vacation = $this->user->vacation;
				}

				$buildsId = [4, 12, 212];

				foreach (Vars::getResources() as $res) {
					$buildsId[] = Vars::getIdByName($res . '_mine');
				}

				$this->user->planets->each(function (Planet $planet) use ($buildsId) {
					$planet->entities->whereIn('id', $buildsId)->each(fn(PlanetEntity $entity) => $entity->factor = 0);
					$planet->save();
				});
			}
		}

		$deleteTime = $request->post('delete')
			? ($this->user->delete_time ?? (now()->addDays(7))) : null;

		if (!$this->user->isVacation()) {
			$sex = ($request->post('sex', 'M') == 'F') ? 2 : 1;

			$color = $request->post('color', 1);
			$color = max(1, min(13, $color));

			$timezone = $request->post('timezone', 0);

			if ($timezone < -12 || $timezone > 12) {
				$timezone = null;
			}

			if ($timezone !== null) {
				$timezone = (int) $timezone;
			}

			$SetSort = $request->post('settings_sort', 0);
			$SetOrder = $request->post('settings_order', 0);
			$about = Format::text($request->post('text', ''));
			$spy = $request->post('spy', 1);

			if ($spy < 1 || $spy > 1000) {
				$spy = 1;
			}

			$this->user->sex = $sex;
			$this->user->vacation = $vacation;
			$this->user->delete_time = $deleteTime;

			$this->user->setOption('records', !empty($request->post('records')));
			$this->user->setOption('bb_parser', !empty($request->post('bbcode')));
			$this->user->setOption('chatbox', !empty($request->post('chatbox')));
			$this->user->setOption('only_available', !empty($request->post('available')));
			$this->user->setOption('planetlist', $request->post('planetlist'));
			$this->user->setOption('planetlistselect', $request->post('planetlistselect'));
			$this->user->setOption('planet_sort', (int) $SetSort);
			$this->user->setOption('planet_sort_order', (int) $SetOrder);
			$this->user->setOption('color', (int) $color);
			$this->user->setOption('timezone', $timezone);
			$this->user->setOption('spy', (int) $spy);

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

			$this->user->about = $about;
			$this->user->locale = $request->post('locale');
			$this->user->update();

			Cache::forget('app::planetlist_' . $this->user->id);
		} else {
			$this->user->vacation = $vacation;
			$this->user->delete_time = $deleteTime;

			$this->user->update();
		}
	}

	public function password(ChangePasswordRequest $request)
	{
		$this->user->password = Hash::make($request->input('password'));
		$this->user->save();

		Auth::logout();
	}
}
