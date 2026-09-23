<?php

namespace App\Http\Controllers;

use App\Facades\Vars;
use App\Models;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class StartController extends Controller
{
	public function index()
	{
		if ($this->isComplete()) {
			return to_route('overview');
		}

		return Inertia::render('Start');
	}

	public function save(Request $request)
	{
		if ($this->isComplete()) {
			return to_route('overview');
		}

		$data = $request->validate([
			'name' => 'required|string|max:30',
			'locale' => 'required|string|in:ru,en',
			'race' => 'required|integer|between:1,4',
			'avatar' => ['required', 'string', 'regex:/\A[12]_[1-8]\z/'],
		]);

		$data['name'] = strip_tags(trim($data['name']));

		if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-!~.@ ]+$/u", $data['name'])) {
			throw ValidationException::withMessages(['name' => __('start.error_charalpha')]);
		}

		$existUser = Models\User::query()
			->where('username', $data['name'])
			->whereKeyNot($this->user->id)
			->exists();

		if ($existUser) {
			throw ValidationException::withMessages(['name' => __('main.reg_error_userexist')]);
		}

		[$sex, $avatar] = explode('_', $data['avatar']);

		$this->user->username = $data['name'];
		$this->user->locale = $data['locale'];
		$this->user->race = (int) $data['race'];
		$this->user->sex = (int) $sex;
		$this->user->avatar = (int) $avatar;
		$this->user->daily_bonus = now()->addDay();

		foreach (Vars::getOfficiers() as $code) {
			$this->user->setAttribute('officier_' . $code, now()->addDays(7));
		}

		$this->user->update();

		return to_route('quests');
	}

	private function isComplete(): bool
	{
		return $this->user->race && $this->user->sex && $this->user->avatar;
	}
}
