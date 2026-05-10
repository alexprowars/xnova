<?php

namespace App\Models;

use App\Engine\Enums\AllianceAccess;
use App\Models\Observers\AllianceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy([AllianceObserver::class])]
class Alliance extends Model implements HasMedia
{
	use InteractsWithMedia;
	use SoftDeletes;

	protected $table = 'alliances';
	protected $guarded = [];

	public array $rights = [];
	public ?AllianceMember $member;

	protected $casts = [
		'ranks' => 'json:unicode',
	];

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

	public function registerMediaCollections(): void
	{
		$this->addMediaCollection('default')
			->storeConversionsOnDisk('resize')
			->singleFile()
			->useDisk('media');
	}

	public function registerMediaConversions(?Media $media = null): void
	{
		$this->addMediaConversion('thumb')->width(500)->height(500);
	}

	public function getRanks(): void
	{
		if (empty($this->ranks)) {
			$this->setAttribute('ranks', []);
		}
	}

	public function getMember(User $user): ?AllianceMember
	{
		$this->member ??= $this->members()->whereBelongsTo($user)->first();

		return $this->member;
	}

	public function parseRights(?int $userId = null): void
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

	public function canAccess(AllianceAccess $method): bool
	{
		if (!count($this->rights)) {
			$this->parseRights();
		}

		return $this->rights[$method->value] ?? false;
	}

	public function deleteMember(int $userId): void
	{
		$this->decrement('members');
		AllianceMember::query()->where('user_id', $userId)->delete();

		Planet::query()->where('user_id', $userId)->update(['alliance_id' => null]);
		User::query()->where('id', $userId)->update(['alliance_id' => null, 'alliance_name' => null]);
	}
}
