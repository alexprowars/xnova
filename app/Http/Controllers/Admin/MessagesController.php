<?php

namespace App\Http\Controllers\Admin;

use App\Models\Message;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Helpers;

class MessagesController extends Controller
{
	public static function getMenu()
	{
		return [[
			'code'	=> 'messages',
			'title' => 'Сообщения',
			'icon'	=> 'la la-sms',
			'sort'	=> 170
		]];
	}

	public function index(Request $request)
	{
		$parse = [];

		$Prev = $request->has('prev');
		$Next = $request->has('next');
		$DelSel = $request->has('delsel');
		$DelDat = $request->has('deldat');

		$CurrPage = (int) $request->post('curr', 1);
		$Selected = (int) $request->post('type', 1);
		$SelPage = (int) $request->input('p', 1);

		if ($Selected == 6) {
			$Selected = 0;
		}

		$parse['types'] = [1, 2, 3, 4, 5, 0];

		$user = auth()->user();

		if ($user->authlevel == 1) {
			$parse['types'] = [3, 4, 5];
		}

		if (!in_array($Selected, $parse['types'])) {
			$t = $parse['types'];

			$Selected = array_shift($t);

			unset($t);
		}

		$ViewPage = (!empty($SelPage)) ? $SelPage : 1;

		if ($Prev) {
			--$CurrPage;

			if ($CurrPage >= 1) {
				$ViewPage = $CurrPage;
			} else {
				$ViewPage = 1;
			}
		} elseif ($Next) {
			++$CurrPage;

			$ViewPage = $CurrPage;
		} elseif ($DelSel && $user->authlevel > 1) {
			foreach ($request->post('sele_mes') as $MessId => $Value) {
				if ($Value = "on") {
					Message::query()->where('id', $MessId)->delete();
				}
			}
		} elseif ($DelDat && $user->authlevel > 1) {
			$SelDay 	= (int) $request->post('selday');
			$SelMonth 	= (int) $request->post('selmonth');
			$SelYear 	= (int) $request->post('selyear');

			$LimitDate = Carbon::createFromDate($SelYear, $SelMonth, $SelDay);

			Message::query()->where('time', '<=', $LimitDate)->delete();
			Report::query()->where('created_at', '<=', $LimitDate)->delete();
		}

		$Mess = Message::query()->where('type', $Selected)->count();

		$MaxPage = ceil(($Mess / 25));

		$parse['mlst_data_page'] = $ViewPage;
		$parse['mlst_data_pagemax'] = $MaxPage;
		$parse['mlst_data_sele'] = $Selected;

		$messages = Message::query()
			->where('type', $Selected)
			->orderByDesc('time')
			->limit(25)
			->offset((($ViewPage - 1) * 25))
			->with('user');

		if (isset($_POST['userid']) && $_POST['userid'] != "") {
			$messages->where('user_id', (int) $_POST['userid']);
			$parse['userid'] = intval($_POST['userid']);
		} elseif (isset($_POST['userid_s']) && $_POST['userid_s'] != "") {
			$messages->where('from_id', (int) $_POST['userid_s']);
			$parse['userid_s'] = intval($_POST['userid_s']);
		}

		$messages = $messages->get();

		$parse['items'] = [];

		foreach ($messages as $row) {
			$bloc['id'] = $row->id;
			$bloc['from'] = $row->from_id;
			$bloc['to'] = $row->user ? $row->user->username . ' ID:' . $row->user_id : '-';
			$bloc['text'] = stripslashes(nl2br($row->text));
			$bloc['time'] = $row->time?->format('d.m.Y H:i:s');

			$parse['items'][] = $bloc;
		}

		if (isset($_POST['delit']) && $user->authlevel > 1) {
			Message::query()->where('id', $_POST['delit'])->delete();
			$this->message(_getText('mlst_mess_del') . " ( " . $_POST['delit'] . " )", _getText('mlst_title'), "/messages/", 3);
		}

		$pagination = Helpers::pagination($Mess, 25, '/admin/messages/', $ViewPage);

		View::share('title', __('admin.mlst_title'));

		return view('admin.messages', ['parse' => $parse, 'pagination' => $pagination]);
	}
}
