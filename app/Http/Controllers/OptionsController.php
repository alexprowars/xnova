<?php

namespace App\Http\Controllers;

use Gumlet\ImageResize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Exceptions\ErrorException;
use App\Exceptions\RedirectException;
use App\Files;
use App\Format;
use App\Game;
use App\Helpers;
use App\Mail\UserLostPasswordSuccess;
use App\Models;
use App\User;
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

				$check = Models\Authentication::query()
					->where('provider_id', $identity)->exists();

				if (!$check) {
					Models\Authentication::query()->insert([
						'user_id' => $this->user->getId(),
						'provider_id' => $identity,
						'create_time' => time(),
					]);
				} else {
					throw new RedirectException('Данная точка входа уже используется', '/options/');
				}
			} else {
				throw new RedirectException('Ошибка получения данных', '/options/');
			}
		}

		throw new RedirectException('', '/options/');
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

			throw new RedirectException('Заявка отправлена на рассмотрение', '/options/');
		}
	}

	public function save(Request $request)
	{
		$userInfo = Models\UserDetail::query()->find($this->user->id);

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

		if ($this->user->vacation > time()) {
			$vacation = $this->user->vacation;
		} else {
			$vacation = 0;

			if ($request->post('vacation')) {
				$queueManager = new Queue($this->user);
				$queueCount = $queueManager->getCount();

				$UserFlyingFleets = Models\Fleet::query()->where('owner', $this->user->id)->count();

				if ($queueCount > 0) {
					throw new ErrorException('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe. Строится: ' . $queueCount . ' объектов.');
				} elseif ($UserFlyingFleets > 0) {
					throw new ErrorException('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.');
				} else {
					if ($this->user->vacation == 0) {
						$vacation = time() + config('settings.vocationModeTime', 172800);
					} else {
						$vacation = $this->user->vacation;
					}

					$buildsId = [4, 12, 212];

					foreach (Vars::getResources() as $res) {
						$buildsId[] = Vars::getIdByName($res . '_mine');
					}

					Models\PlanetBuilding::query()->whereIn('planet_id', User::getPlanetsId($this->user->id))
						->whereIn('build_id', $buildsId)
						->update(['power' => 0]);

					Models\PlanetUnit::query()->whereIn('planet_id', User::getPlanetsId($this->user->id))
						->whereIn('unit_id', $buildsId)
						->update(['power' => 0]);
				}
			}
		}

		$Del_Time = $request->post('delete') ? (time() + 604800) : 0;

		if (!$this->user->isVacation()) {
			$sex = ($request->post('sex', 'M') == 'F') ? 2 : 1;

			$color = $request->post('color', 1);
			$color = max(1, min(13, $color));

			$timezone = $request->post('timezone', 0);

			if ($timezone < -32 || $timezone > 16) {
				$timezone = 0;
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
			$this->user->deltime = $Del_Time;

			$this->user->setOption('records', !empty($request->post('records')));
			$this->user->setOption('bb_parser', !empty($request->post('bbcode')));
			$this->user->setOption('chatbox', !empty($request->post('chatbox')));
			$this->user->setOption('only_available', !empty($request->post('available')));
			$this->user->setOption('planetlist', $request->post('planetlist'));
			$this->user->setOption('planetlistselect', $request->post('planetlistselect'));
			$this->user->setOption('planet_sort', (int) $SetSort);
			$this->user->setOption('planet_sort_order', (int) $SetOrder);
			$this->user->setOption('color', (int) $color);
			$this->user->setOption('timezone', (int) $timezone);
			$this->user->setOption('spy', (int) $spy);

			$this->user->update();

			if ($request->hasFile('image')) {
				$file = $request->file('image');

				if ($file->isValid()) {
					$fileType = $file->getMimeType();

					if (!str_contains($fileType, 'image/')) {
						throw new ErrorException('Разрешены к загрузке только изображения');
					}

					if ($userInfo->image > 0) {
						Files::delete($userInfo->image);
					}

					$userInfo->image = Files::save($file);

					$f = Files::getById($userInfo->image);

					if ($f) {
						$image = new ImageResize($f['path']);
						$image->quality_jpg = 90;
						$image->crop(300, 300, ImageResize::CROPCENTER);
						$image->save($f['path']);
					}
				}
			}

			if ($request->post('image_delete')) {
				if (Files::delete($userInfo->image)) {
					$userInfo->image = 0;
				}
			}

			$userInfo->about = $about;
			$userInfo->update();

			Cache::forget('app::planetlist_' . $this->user->getId());
		} else {
			$this->user->vacation = $vacation;
			$this->user->deltime = $Del_Time;

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

			throw new RedirectException('Пароль успешно изменён', '/');
		}

		if ($this->user->username != $username) {
			if ($userInfo->username_last > (time() - 86400)) {
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
			$this->user->update();

			$userInfo->username_last = time();
			$userInfo->update();

			throw new RedirectException('Имя пользователя изменено', '/options/');
		}

		throw new RedirectException(__('options.succeful_save'), '/options/');
	}

	public function index()
	{
		$userInfo = Models\UserDetail::query()->find($this->user->id);

		$parse = [];
		$parse['vacation'] = $this->user->vacation > 0;

		if ($this->user->vacation > 0) {
			$parse['um_end_date'] = Game::datezone("d.m.Y H:i:s", $this->user->vacation);
			$parse['opt_delac_data'] = ($this->user->deltime > 0);
			$parse['opt_modev_data'] = ($this->user->vacation > 0);
			$parse['opt_usern_data'] = $this->user->username;
		} else {
			$parse['options'] = $this->user->getOptions();
			$parse['avatar'] = '';

			if ($userInfo->image > 0) {
				$file = Files::getById($userInfo->image);

				if ($file) {
					$parse['avatar'] = $file['src'];
				}
			}

			$parse['opt_usern_datatime'] = $userInfo->username_last < (time() - 86400);
			$parse['opt_usern_data'] = $this->user->username;
			$parse['opt_mail_data'] = $this->user->email;
			$parse['opt_isemail'] = Helpers::is_email($this->user->email);

			$parse['opt_delac_data'] = $this->user->deltime > 0;
			$parse['opt_modev_data'] = $this->user->vacation > 0;

			$parse['sex'] = $this->user->sex;
			$parse['about'] = preg_replace('!<br.*>!iU', "\n", $userInfo->about);

			$parse['auth'] = [];

			/*$authData = DB::select("SELECT * FROM users_auth WHERE user_id = " . $this->user->getId() . "");

			$parse['auth'] = array_map(function ($value) {
				return (array) $value;
			}, $authData);*/
		}

		return $parse;
	}
}
