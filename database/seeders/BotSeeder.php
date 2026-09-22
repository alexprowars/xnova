<?php

namespace Database\Seeders;

use App\Engine\Ai\StrategyType;
use App\Engine\Coordinates;
use App\Facades\Galaxy;
use App\Models\Ai;
use App\Models\Planet;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Nubs\RandomNameGenerator;
use RuntimeException;

class BotSeeder extends Seeder
{
	private const int BOT_COUNT = 500;
	private const int GALAXY = 2;

	public function run(): void
	{
		$pending = [];

		for ($number = 1; $number <= self::BOT_COUNT; $number++) {
			$email = 'galaxy-bot-' . $number . '@example.invalid';

			$user = User::withTrashed()
				->where('email', $email)
				->first();

			if ($user) {
				if ($user->trashed() || !Ai::query()->whereBelongsTo($user)->exists()) {
					throw new RuntimeException('Адрес ' . $email . ' уже занят другим или удалённым игроком.');
				}

				continue;
			}

			$pending[$number] = $email;
		}

		if (empty($pending)) {
			$this->command->info('Все ' . self::BOT_COUNT . ' ботов уже созданы.');

			return;
		}

		$positions = $this->getPositions(count($pending));
		$strategies = StrategyType::cases();
		$created = 0;

		foreach ($pending as $number => $email) {
			$coordinates = $positions[$created];
			$strategy = $strategies[($number - 1) % count($strategies)];

			DB::transaction(function () use ($number, $email, $coordinates, $strategy) {
				$user = UserService::creation([
					'name' => new RandomNameGenerator\Alliteration()->getName(),
					'email' => $email,
					'password' => Str::random(40),
				]);

				$user->update([
					'race' => ($number - 1) % 4 + 1,
					'sex' => random_int(1, 2),
					'avatar' => random_int(1, 8),
				]);

				$planet = Galaxy::createPlanet($coordinates, $user, 'Bot ' . $number, true);

				if (!$planet) {
					throw new RuntimeException('Не удалось создать планету бота ' . $number . ': позиция уже занята.');
				}

				$user->setMainPlanet($planet);

				Ai::create([
					'user_id' => $user->id,
					'active' => true,
					'strategy' => $strategy,
				]);
			});

			$created++;

			if ($created % 100 === 0 || $created === count($pending)) {
				$this->command->info('Создано ботов: ' . $created . ' / ' . count($pending));
			}
		}
	}

	/** @return list<Coordinates> */
	private function getPositions(int $count): array
	{
		$positions = [];

		$maxSystems = (int) config('game.maxSystemInGalaxy');
		$maxPlanets = (int) config('game.maxPlanetInSystem');
		$maxBotsPerSystem = (int) config('ai.max_bots_per_system', 3);

		$botsPerSystem = Planet::query()
			->where('galaxy', self::GALAXY)
			->whereIn('user_id', Ai::query()->select('user_id'))
			->select('system')
			->selectRaw('COUNT(DISTINCT user_id) as bot_count')
			->groupBy('system')
			->pluck('bot_count', 'system');

		for ($system = 1; $system <= $maxSystems; $system++) {
			$availableSlots = $maxBotsPerSystem - ($botsPerSystem[$system] ?? 0);

			if ($availableSlots <= 0) {
				continue;
			}

			$freePositions = Galaxy::getFreePositions(
				new Coordinates(self::GALAXY, $system),
				(int) round($maxPlanets * 0.2),
				(int) round($maxPlanets * 0.8)
			);

			shuffle($freePositions);

			foreach (array_slice($freePositions, 0, $availableSlots) as $position) {
				$positions[] = new Coordinates(self::GALAXY, $system, $position);

				if (count($positions) === $count) {
					return $positions;
				}
			}
		}

		throw new RuntimeException('В галактике ' . self::GALAXY . ' недостаточно свободных позиций для ' . $count . ' ботов при лимите ' . $maxBotsPerSystem . ' бота на систему.');
	}
}
