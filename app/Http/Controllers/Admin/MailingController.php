<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Xnova\Models\User;
use Xnova\User;

class MailingController extends Controller
{
	use ValidatesRequests;

	public static function getMenu()
	{
		return [[
			'code'	=> 'mailing',
			'title' => 'Рассылка',
			'icon'	=> 'edit',
			'sort'	=> 180
		]];
	}

	public function index(Request $request)
	{
		if ($request->isMethod('POST')) {
			$fields = $this->validate($request, [
				'message' => 'required',
				'theme' => 'required',
			], [
				'required' => 'Поле ":attribute" обязательно для заполнения',
			]);

			/** @var User $currentUser */
			$currentUser = Auth::user();

			if ($currentUser->isAdmin()) {
				$color = 'yellow';
			} else {
				$color = 'skyblue';
			}

			$users = User::query()->get(['id']);

			foreach ($users as $user) {
				User::sendMessage(
					$user->id,
					false,
					time(),
					1,
					'<font color="' . $color . '">Информационное сообщение (' . $currentUser->username . ')</font>',
					$fields['message']
				);
			}

			return redirect(backpack_url('mailing'))->with('success', 'Сообщение успешно отправлено всем игрокам!');
		}

		View::share('title', 'Рассылка');
		View::share('breadcrumbs', [
			'Панель управления' => backpack_url('/'),
			'Рассылка' => false,
		]);

		return view('admin.mailing');
	}
}
