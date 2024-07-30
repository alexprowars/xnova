<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Http\Controllers\Controller;
use App\Models\Alliance;
use App\Models\AllianceDiplomacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllianceDiplomacyController extends Controller
{
	use AllianceControllerTrait;

	public function index()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::DIPLOMACY_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$parse['DText'] = $parse['DMyQuery'] = $parse['DQuery'] = [];

		$dp = DB::select("SELECT ad.*, a.name FROM alliances_diplomacies ad, alliances a WHERE a.id = ad.diplomacy_id AND ad.alliance_id = '" . $alliance->id . "'");

		foreach ($dp as $diplo) {
			if ($diplo->status == 0) {
				if ($diplo->primary == 1) {
					$parse['DMyQuery'][] = (array) $diplo;
				} else {
					$parse['DQuery'][] = (array) $diplo;
				}
			} else {
				$parse['DText'][] = (array) $diplo;
			}
		}

		$parse['items'] = [];

		$alliances = Alliance::query()->whereNot('id', $this->user->alliance_id)
			->where('members_count', '>', 0)
			->get();

		foreach ($alliances as $ally) {
			$parse['items'][] = $ally->only(['id', 'name', 'tag']);
		}

		return response()->state($parse);
	}

	public function accept(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::DIPLOMACY_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$item = $alliance->diplomacy()->where('id', (int) $request->query('id'))
			->first();

		if (!$item) {
			throw new Exception('Ошибка ввода параметров');
		}

		AllianceDiplomacy::query()->where('alliance_id', $item->alliance_id)
			->where('diplomacy_id', $item->diplomacy_id)
			->update(['status' => 1]);

		AllianceDiplomacy::query()->where('alliance_id', $item->diplomacy_id)
			->where('diplomacy_id', $item->alliance_id)
			->update(['status' => 1]);
	}

	public function reject(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::DIPLOMACY_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$item = $alliance->diplomacy()->where('id', (int) $request->query('id'))
			->first();

		if (!$item) {
			throw new Exception('Ошибка ввода параметров');
		}

		AllianceDiplomacy::query()->where('alliance_id', $item->alliance_id)
			->where('diplomacy_id', $item->diplomacy_id)
			->delete();

		AllianceDiplomacy::query()->where('alliance_id', $item->diplomacy_id)
			->where('diplomacy_id', $item->alliance_id)
			->delete();
	}

	public function create(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::DIPLOMACY_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$stts = (int) $request->post('status', 0);
		$ally = Alliance::find((int) $request->post('alliance'));

		if (!$ally) {
			throw new Exception('Ошибка ввода параметров');
		}

		$ad = $alliance->diplomacy()
			->where('diplomacy_id', $ally->id)
			->count();

		if ($ad) {
			throw new Exception('У вас уже есть соглашение с этим альянсом. Разорвите старое соглашения прежде чем создать новое.');
		}

		if ($stts < 0 || $stts > 3) {
			$st = 0;
		}

		$alliance->diplomacy()->create([
			'diplomacy_id' => $ally->id,
			'type' => $stts,
			'status' => 0,
			'primary' => 1,
		]);

		$alliance->diplomacy()->create([
			'diplomacy_id' => $alliance->id,
			'type' => $stts,
			'status' => 0,
			'primary' => 0,
		]);
	}
}
