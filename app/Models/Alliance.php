<?php

namespace App\Models;

use App\Engine\Enums\AllianceAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Alliance extends Model
{
	protected $table = 'alliances';
	protected $guarded = false;

	public $rights = [];
	public ?AllianceMember $member;

	protected $casts = [
		'ranks' => 'json:unicode',
	];

	protected static function booted()
	{
		static::deleted(function (Alliance $model) {
			User::query()->where('alliance_id', $model->id)
				->update(['alliance_id' => null, 'alliance_name' => null]);

			Statistic::query()->where('stat_type', 1)
				->where('user_id', null)
				->where('alliance_id', $model->id)
				->delete();
		});
	}

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/** @return HasMany<AllianceMember, $this> */
	public function members(): HasMany
	{
		return $this->hasMany(AllianceMember::class);
	}

	/** @return HasMany<AllianceRequest, $this> */
	public function requests(): HasMany
	{
		return $this->hasMany(AllianceRequest::class);
	}

	/** @return HasMany<AllianceDiplomacy, $this> */
	public function diplomacy(): HasMany
	{
		return $this->hasMany(AllianceDiplomacy::class);
	}

	public function getRanks()
	{
		if (empty($this->ranks)) {
			$this->setAttribute('ranks', []);
		}
	}

	public function getMember(User $user)
	{
		$this->member ??= $this->members()->whereBelongsTo($user)->first();

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
		User::query()->where('id', $userId)->update(['alliance_id' => null, 'alliance_name' => null]);
	}
}
