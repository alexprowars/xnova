<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use App\Engine\Ai\Development\StrategyType;
use App\Models\Ai;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AiController extends AdminController
{
	public function index(Request $request)
	{
		Access::authorize('ai');

		return $this->listing(
			$request,
			'ai',
			Ai::query()->with('user'),
			['id', 'user', 'active', 'strategy', 'next_run_at'],
			[Presentation::field('id'), Presentation::field('user_id')],
			fn(Ai $ai) => [
				...Presentation::record($ai, ['active', 'next_run_at']),
				'strategy' => $ai->strategy->getLabel(),
				'user' => Presentation::userLink($ai->user),
			],
		);
	}

	private function fields(?Ai $ai = null): array
	{
		return Presentation::fill(
			[
				Presentation::field('active', 'checkbox', [], false),
				Presentation::field(
					'strategy',
					'select',
					array_map(
						fn(StrategyType $type) => ['value' => $type->value, 'label' => $type->getLabel()],
						StrategyType::cases(),
					),
					'balanced',
				),
			],
			$ai,
		);
	}

	public function create()
	{
		Access::authorize('ai');

		return $this->form('ai', $this->fields());
	}

	public function edit(Ai $ai)
	{
		Access::authorize('ai');

		return $this->form('ai', $this->fields($ai), $ai->id, [
			'links' => [Presentation::userLink($ai->user)],
		]);
	}

	public function store(Request $request)
	{
		return $this->update($request, new Ai());
	}

	public function update(Request $request, Ai $ai)
	{
		Access::authorize('ai');

		$data = $request->validate([
			'active' => 'required|boolean',
			'strategy' => ['required', Rule::enum(StrategyType::class)],
		]);

		DB::transaction(function () use ($ai, $data) {
			if (!$ai->exists) {
				$user = UserService::creation([
					'name' => 'Bot ' . Str::random(8),
					'email' => Str::uuid() . '@local',
					'password' => Str::random(40),
				]);

				$user->update([
					'race' => random_int(1, 4),
					'sex' => random_int(1, 2),
					'avatar' => random_int(1, 8),
				]);

				$ai->user_id = $user->id;
			}

			$ai->fill($data)->save();
		});

		return redirect('/admin/ai/' . $ai->id . '/edit')->with('admin_notice', __('admin.saved'));
	}
}
