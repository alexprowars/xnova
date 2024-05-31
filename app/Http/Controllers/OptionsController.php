<?php

namespace App\Http\Controllers;

use Gumlet\ImageResize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Exceptions\ErrorException;
use App\Exceptions\RedirectException;
use App\Files;
use App\Format;
use App\Helpers;
use App\Mail\UserLostPasswordSuccess;
use App\Models;
use App\Models\User;
use App\Queue;
use App\Controller;
use App\Vars;

class OptionsController extends Controller
{
	public function externalAction()
	{
		$token = request()->input('token', '');

		if (!empty($token)) {
			$s = file_get_contents('http://u-login.com/token.php?token=' . $_REQUEST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
			$data = json_decode($s, true);

			if (isset($data['identity'])) {
				$identity = isset($data['profile']) && $data['profile'] != '' ? $data['profile'] : $data['identity'];

				$check = Models\UserAuthentication::query()
					->where('provider_id', $identity)->exists();

				if (!$check) {
					Models\UserAuthentication::create([
						'user_id' => $this->user->id,
						'provider_id' => $identity,
					]);
				} else {
					throw new RedirectException('/options', 'Данная точка входа уже используется');
				}
			} else {
				throw new RedirectException('/options', 'Ошибка получения данных');
			}
		}

		throw new RedirectException('/options');
	}

	public function emailAction(Request $request)
	{
		if ($request->post('password') && $request->post('email')) {
			if (!Hash::check($request->post('password'), $this->user->password)) {
				throw new ErrorException('Heпpaвильный тeкyщий пapoль');
			}

			$existEmail = Models\User::query()->where('email', addslashes(htmlspecialchars(trim($request->post('email')))))
				->exists();

			if ($existEmail) {
				throw new ErrorException('Данный email уже используется в игре.');
			}

			User::sendMessage(1, false, time(), 5, $this->user->username, 'Поступила заявка на смену Email от ' . $this->user->username . ' на ' . addslashes(htmlspecialchars($request->post('email'))) . '. <a href="' . URL::to('admin/email/') . '">Сменить</a>');

			throw new RedirectException('/options', 'Заявка отправлена на рассмотрение');
		}
	}

	public function save(Request $request)
	{
		if (
			$request->post('username')
			&& trim($request->post('username')) != ''
			&& trim($request->post('username')) != $this->user->username
			&& mb_strlen(trim($request->post('username')), 'UTF-8') > 3
		) {
			$username = preg_replace("/([\s\x{0}\x{0B}]+)/iu", " ", trim($request->post('username')));

			if (preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-!~.@ ]+$/u", $username)) {
				$username = addslashes($username);
			} else {
				$username = $this->user->username;
			}
		} else {
			$username = $this->user->username;
		}

		if ($request->post('email') && !Helpers::is_email($this->user->email) && Helpers::is_email($request->post('email'))) {
			$e = addslashes(htmlspecialchars(trim($request->post('email'))));

			$existEmail = Models\User::query()->where('email', $e)
				->exists();

			if ($existEmail) {
				throw new ErrorException('Данный email уже используется в игре.');
			}

			$password = Str::random(10);

			$this->user->email = $e;
			$this->user->password = Hash::make($password);
			$this->user->save();

			Mail::to($e)->send(new UserLostPasswordSuccess([
				'#EMAIL#' => $e,
				'#PASSWORD#' => $password,
			]));

			throw new ErrorException('Ваш пароль от аккаунта: ' . $password . '. Обязательно смените его на другой в настройках игры. Копия пароля отправлена на указанный вами электронный почтовый ящик.');
		}

		if ($this->user->vacation?->isFuture()) {
			$vacation = $this->user->vacation;
		} else {
			$vacation = null;

			if ($request->post('vacation')) {
				$queueManager = new Queue($this->user);
				$queueCount = $queueManager->getCount();

				$UserFlyingFleets = Models\Fleet::query()->where('user_id', $this->user->id)->count();

				if ($queueCount > 0) {
					throw new ErrorException('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe. Строится: ' . $queueCount . ' объектов.');
				} elseif ($UserFlyingFleets > 0) {
					throw new ErrorException('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.');
				} else {
					if (!$this->user->vacation) {
						$vacation = now()->addDays(config('settings.vacationModeTime', 2));
					} else {
						$vacation = $this->user->vacation;
					}

					$buildsId = [4, 12, 212];

					foreach (Vars::getResources() as $res) {
						$buildsId[] = Vars::getIdByName($res . '_mine');
					}

					Models\PlanetEntity::query()->whereIn('planet_id', User::getPlanetsId($this->user->id))
						->whereIn('build_id', $buildsId)
						->update(['power' => 0]);
				}
			}
		}

		$Del_Time = $request->post('delete') ? (now()->addDays(7)) : null;

		if (!$this->user->isVacation()) {
			$sex = ($request->post('sex', 'M') == 'F') ? 2 : 1;

			$color = $request->post('color', 1);
			$color = max(1, min(13, $color));

			$timezone = $request->post('timezone');

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
			$this->user->delete_time = $Del_Time;

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

			if ($request->hasFile('image')) {
				$file = $request->file('image');

				if ($file->isValid()) {
					$fileType = $file->getMimeType();

					if (!str_contains($fileType, 'image/')) {
						throw new ErrorException('Разрешены к загрузке только изображения');
					}

					if ($this->user->image > 0) {
						Files::delete($this->user->image);
					}

					$this->user->image = Files::save($file);

					$f = Files::getById($this->user->image);

					if ($f) {
						$image = new ImageResize($f['path']);
						$image->quality_jpg = 90;
						$image->crop(300, 300, ImageResize::CROPCENTER);
						$image->save($f['path']);
					}
				}
			}

			if ($request->post('image_delete')) {
				if (Files::delete($this->user->image)) {
					$this->user->image = null;
				}
			}

			$this->user->about = $about;
			$this->user->update();

			Cache::forget('app::planetlist_' . $this->user->id);
		} else {
			$this->user->vacation = $vacation;
			$this->user->delete_time = $Del_Time;

			$this->user->update();
		}

		if (!empty($request->post('password')) && !empty($request->post('new_password'))) {
			if (!Hash::check($request->post('password'), $this->user->password)) {
				throw new ErrorException('Heпpaвильный тeкyщий пapoль');
			}

			if ($request->post('new_password') != $request->post('new_password_confirm')) {
				throw new ErrorException('Bвeдeнныe пapoли нe coвпaдaют');
			}

			$this->user->password = Hash::make($request->post('new_password'));
			$this->user->save();

			Auth::logout();

			throw new RedirectException('/', 'Пароль успешно изменён');
		}

		if ($this->user->username != $username) {
			if ($this->user->username_change?->greaterThan(now()->subDay())) {
				throw new ErrorException('Смена игрового имени возможна лишь раз в сутки.');
			}

			$existName = Models\User::query()->where('username', $username)->exists();

			if ($existName) {
				throw new ErrorException('Дaннoe имя aккayнтa yжe иcпoльзyeтcя в игpe');
			}

			if (!preg_match("/^[a-zA-Za-яA-Я0-9_.,\-!?* ]+$/u", $username) || mb_strlen($username) < 5) {
				throw new ErrorException('Дaннoe имя aккayнтa cлишкoм кopoткoe или имeeт зaпpeщeнныe cимвoлы');
			}

			$this->user->username = $username;
			$this->user->username_change = now();
			$this->user->update();

			throw new RedirectException('/options', 'Имя пользователя изменено');
		}

		throw new RedirectException('/options', __('options.succeful_save'));
	}

	public function index()
	{
		$parse = [];
		$parse['vacation'] = $this->user->isVacation();

		if ($this->user->vacation) {
			$parse['um_end_date'] = $this->user->vacation->utc()->toAtomString();
			$parse['opt_delac_data'] = !empty($this->user->delete_time);
			$parse['opt_modev_data'] = $this->user->isVacation();
			$parse['opt_usern_data'] = $this->user->username;
		} else {
			$parse['options'] = $this->user->getOptions();
			$parse['avatar'] = '';

			if ($this->user->image > 0) {
				$file = Files::getById($this->user->image);

				if ($file) {
					$parse['avatar'] = $file['src'];
				}
			}

			$parse['opt_usern_datatime'] = $this->user->username_change?->lessThan(now()->subDay());
			$parse['opt_usern_data'] = $this->user->username;
			$parse['opt_mail_data'] = $this->user->email;
			$parse['opt_isemail'] = Helpers::is_email($this->user->email);

			$parse['opt_delac_data'] = !empty($this->user->delete_time);
			$parse['opt_modev_data'] = $this->user->isVacation();

			$parse['sex'] = $this->user->sex;
			$parse['about'] = preg_replace('!<br.*>!iU', "\n", $this->user->about);

			$parse['auth'] = [];

			/*$authData = DB::select("SELECT * FROM users_auth WHERE user_id = " . $this->user->getId() . "");

			$parse['auth'] = array_map(function ($value) {
				return (array) $value;
			}, $authData);*/
		}

		return response()->state($parse);
	}
}
