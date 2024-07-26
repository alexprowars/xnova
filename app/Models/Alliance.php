<?php

namespace App\Models;

use App\Engine\Enums\AllianceAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Alliance extends Model
{
	protected $table = 'alliances';
	protected $guarded = false;

	public $rights = [];
	public ?AllianceMember $member;

	protected function casts(): array
	{
		return [
			'ranks' => 'array',
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function members()
	{
		return $this->hasMany(AllianceMember::class);
	}

	public function requests()
	{
		return $this->hasMany(AllianceRequest::class);
	}

	public function diplomacy()
	{
		return $this->hasMany(AllianceDiplomacy::class);
	}

	public function getRanks()
	{
		if (empty($this->ranks)) {
			$this->ranks = [];
		}
	}

	public function getMember($userId)
	{
		$this->member ??= $this->members()->where('user_id', $userId)->first();

		return $this->member;
	}

	public function parseRights($userId = 0)
	{
		if (!$userId && Auth::check()) {
			$userId = Auth::user()->id;
		}

		$this->rights = [];

		foreach (AllianceAccess::cases() as $case) {
			$this->rights[$case->value] = false;
		}

		if ($this->user_id == $userId) {
			foreach ($this->rights as $key => $value) {
				$this->rights[$key] = true;
			}
		} elseif (isset($this->ranks[$this->member->rank])) {
			foreach (AllianceAccess::cases() as $case) {
				$this->rights[$case->value] = $this->ranks[$this->member->rank][$case->value] == 1;
			}
		}
	}

	public function canAccess(AllianceAccess $method)
	{
		if (!count($this->rights)) {
			$this->parseRights();
		}

		return $this->rights[$method->value] ?? false;
	}

	public function deleteMember($userId)
	{
		$this->decrement('members');
		AllianceMember::query()->where('user_id', $userId)->delete();

		Planet::query()->where('user_id', $userId)->update(['alliance_id' => null]);
		User::query()->find($userId)->update(['alliance_id' => null, 'alliance_name' => null]);
	}

	public function delete()
	{
		User::where('alliance_id', $this->id)
			->update(['alliance_id' => null, 'alliance_name' => null]);

		parent::delete();

		Statistic::query()->where('stat_type', 1)
			->where('user_id', null)
			->where('alliance_id', $this->id)->delete();
	}
}
