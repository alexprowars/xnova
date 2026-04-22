<?php

namespace App\Services;

use App\Engine\Enums\MessageType;
use App\Engine\Locale;
use App\Engine\Messages\Types\NewLevelMessage;
use App\Exceptions\Exception;
use App\Models\LogsCredit;
use App\Models\Referal;
use App\Models\User;
use App\Notifications\SystemMessage;
use App\Notifications\UserRegistrationNotification;
use App\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Throwable;

class UserService
{
	public static function creation(array $data, bool $notify = false): User
	{
		if (empty($data['password'])) {
			$data['password'] = Str::random(10);
		}

		return DB::transaction(static function () use ($data, $notify) {
			$user = User::create([
				'email' => $data['email'] ?? '',
				'password' => Hash::make($data['password']),
				'username' => $data['name'] ?? '',
				'ip' => Request::ip(),
				'daily_bonus' => now(),
				'onlinetime' => now(),
				'locale' => Locale::getPreferredLocale(),
			]);

			if (!$user->id) {
				throw new Exception('create user error');
			}

			if (Session::has('ref')) {
				$refer = User::query()->whereKeyNot($user)
					->findOne((int) Session::get('ref'));

				if ($refer) {
					Referal::insert([
						'referal_id' => $user->id,
						'user_id' => $refer->id,
					]);
				}
			}

			$settings = app(Settings::class);
			$settings->usersTotal++;
			$settings->save();

			if ($notify && !empty($user->email)) {
				try {
					$user->notify(new UserRegistrationNotification($data['password']));
				} catch (Throwable) {
				}
			}

			return $user;
		});
	}

	public static function checkLevelXp(User $user): void
	{
		$indNextXp = $user->lvl_minier ** 3;
		$warNextXp = $user->lvl_raid ** 2;

		$giveCredits = 0;

		if ($user->xpminier >= $indNextXp && $user->lvl_minier < config('game.level.max_ind', 100)) {
			$giveCredits += self::checkMineLevel($user->id);
		}

		if ($user->xpraid >= $warNextXp && $user->lvl_raid < config('game.level.max_war', 100)) {
			$giveCredits += self::checkRaidLevel($user->id);
		}

		if ($giveCredits > 0) {
			$user->refresh();

			LogsCredit::create([
				'user_id' 	=> $user->id,
				'amount' 	=> $giveCredits,
				'type' 		=> 4,
			]);

			$reffer = Referal::query()
				->whereBelongsTo($user, 'referal')
				->first();

			if ($reffer) {
				$credits = round($giveCredits / 2);
				$reffer->user()->increment('credits', $credits);

				LogsCredit::create([
					'user_id'	=> $reffer->user_id,
					'amount' 	=> $credits,
					'type' 		=> 3,
				]);
			}
		}
	}

	private static function checkMineLevel(int $userId): int
	{
		return DB::transaction(static function () use ($userId) {
			$user = User::lockForUpdate()->find($userId);

			$nextXp = $user->lvl_minier ** 3;

			if ($user->xpminier >= $nextXp && $user->lvl_minier < config('game.level.max_ind', 100)) {
				$user->lvl_minier++;
				$user->credits += config('game.level.credits', 10);
				$user->xpminier -= $nextXp;
				$user->update();

				$user->notify(new SystemMessage(MessageType::System, new NewLevelMessage(['type' => 'mine', 'level' => $user->lvl_minier])));

				return config('game.level.credits', 10);
			}

			return 0;
		});
	}

	private static function checkRaidLevel(int $userId): int
	{
		return DB::transaction(static function () use ($userId) {
			$user = User::lockForUpdate()->find($userId);

			$nextXp = $user->lvl_raid ** 2;

			if ($user->xpraid >= $nextXp && $user->lvl_raid < config('game.level.max_war', 100)) {
				$user->lvl_raid++;
				$user->credits += config('game.level.credits', 10);
				$user->xpraid -= $nextXp;
				$user->update();

				$user->notify(new SystemMessage(MessageType::System, new NewLevelMessage(['type' => 'raid', 'level' => $user->lvl_raid])));

				return config('game.level.credits', 10);
			}

			return 0;
		});
	}
}
