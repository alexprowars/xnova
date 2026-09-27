<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Settings;

class SettingsController extends AdminController
{
	public function edit(Settings $settings)
	{
		Access::authorize('settings');

		$fields = [];

		foreach ($settings->toArray() as $key => $value) {
			$fields[] = Presentation::field(
				$key,
				$key === 'globalMessage' ? 'textarea' : 'number',
				[],
				$value,
			);
		}

		return Inertia::render('Admin/Edit', [
			'resource' => 'settings',
			'title' => __('admin.settings'),
			'fields' => $fields,
			'url' => '/admin/settings',
			'tables' => [],
			'links' => [],
		]);
	}

	public function update(Request $request, Settings $settings)
	{
		Access::authorize('settings');

		$rules = [];

		foreach ($settings->toArray() as $key => $value) {
			$rules[$key] = $key === 'globalMessage'
				? 'nullable|string|max:50000'
				: 'required|integer|min:0|max:2147483647';
		}

		$settings->fill($request->validate($rules))->save();

		return back()->with('admin_notice', __('admin.saved'));
	}
}
