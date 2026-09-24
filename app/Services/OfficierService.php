<?php

namespace App\Services;

use App\Exceptions\Exception;
use App\Facades\Vars;
use App\Models\LogsCredit;
use App\Models\User;
use Carbon\CarbonImmutable;

class OfficierService
{
	public static function getPrice(int $days): int
	{
		return match ($days) {
			7 => 20,
			14 => 40,
			30 => 80,
			default => throw new Exception(__('officier.invalid_parameters')),
		};
	}

	public static function activate(User $user, string $code, int $duration): void
	{
		if (!in_array($code, Vars::getOfficiers(), true)) {
			throw new Exception(__('officier.invalid_item'));
		}

		$now = CarbonImmutable::now()
			->startOfSecond();

		$expiresAt = $user->{'officier_' . $code};

		$date = $expiresAt?->greaterThan($now)
			? $expiresAt : $now;

		$user->{'officier_' . $code} = $date->addSeconds($duration);
	}

	public static function buy(User $user, string $code, int $duration): bool
	{
		$credits = self::getPrice($duration);

		if (!in_array($code, Vars::getOfficiers(), true)) {
			throw new Exception(__('officier.invalid_item'));
		}

		return $user->getConnection()->transaction(function () use ($user, $code, $duration, $credits): bool {
			$user->refreshForUpdate();

			if ($user->credits < $credits) {
				return false;
			}

			if ($code === 'geologist') {
				$purchasedAt = CarbonImmutable::now()
					->startOfSecond();

				$planets = $user->planets()
					->with('entities')
					->orderBy('id')
					->lockForUpdate()
					->get();

				foreach ($planets as $planet) {
					$planet->setRelation('user', $user);
					$planet->getProduction($purchasedAt)->update();
				}
			}

			self::activate($user, $code, $duration * 86400);

			$user->credits -= $credits;
			$user->save();

			LogsCredit::create([
				'user_id' => $user->id,
				'amount' => -$credits,
				'type' => 5,
			]);

			return true;
		});
	}
}
