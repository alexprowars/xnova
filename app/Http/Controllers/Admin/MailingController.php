<?php

namespace App\Http\Controllers\Admin;

use App\Engine\Enums\MessageType;
use App\Notifications\MessageNotification;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\User;

class MailingController extends Controller
{
	use ValidatesRequests;

	public static function getMenu()
	{
		return [[
			'code'	=> 'mailing',
			'title' => 'Рассылка',
			'icon'	=> 'la la-mail-bulk',
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

			$currentUser = Auth::user();

			if ($currentUser->isAdmin()) {
				$color = 'yellow';
			} else {
				$color = 'skyblue';
			}

			$users = User::query()->get(['id']);

			foreach ($users as $user) {
				$user->notify(new MessageNotification(
					null,
					MessageType::System,
					'<font color="' . $color . '">Информационное сообщение (' . $currentUser->username . ')</font>',
					$fields['message']
				));
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
