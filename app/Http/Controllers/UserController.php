<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\UserAuthentication;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function info(): array
	{
		$result = [];
		$result['about'] = preg_replace('!<br.*>!iU', "\n", $this->user->about);
		$result['allow_name_change'] = $this->user->username_change?->lessThan(now()->subDay()) ?? true;
		$result['auth'] = [];

		$authItems = UserAuthentication::query()
			->whereBelongsTo($this->user)
			->get();

		foreach ($authItems as $authItem) {
			$result['auth'][] = [
				'id' => $authItem->id,
				'provider' => $authItem->provider,
				'provider_id' => $authItem->provider_id,
				'created_at' => $authItem->created_at->utc()->toAtomString(),
				'login_date' => $authItem->login_date?->utc()->toAtomString(),
			];
		}

		return $result;
	}

	public function setPlanet(Request $request): void
	{
		$planetId = $request->integer('id');

		if (!$planetId) {
			throw new Exception('planet_id undefined');
		}

		$this->user->setSelectedPlanet($planetId);
	}
}
