<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Presentation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

abstract class AdminController extends Controller
{
	protected function listing(Request $request, string $resource, Builder $query, array $columns, array $filters, callable $row, bool $canCreate = true)
	{
		foreach ($filters as $filter) {
			$key = $filter['key'];
			$value = $request->query($key);

			if (!is_scalar($value) || $value === '') {
				continue;
			}

			if (in_array($key, ['id', 'user_id', 'galaxy', 'system', 'planet', 'type'], true)) {
				$query->where($key, $value);
			} else {
				$query->where($key, 'like', '%' . $value . '%');
			}
		}

		$perPage = in_array($request->integer('per_page'), [10, 20, 50, 100], true)
			? $request->integer('per_page')
			: 20;

		$rows = $query->orderByDesc('id')
			->paginate($perPage)
			->withQueryString()
			->through($row);

		return Inertia::render('Admin/Index', [
			'resource' => $resource,
			'title' => __('admin.' . $resource),
			'table' => Presentation::table(__('admin.' . $resource), Presentation::columns($columns), $rows),
			'filters' => $filters,
			'values' => $request->only(array_column($filters, 'key')),
			'canCreate' => $canCreate,
		]);
	}

	protected function form(string $resource, array $fields, ?int $id = null, array $extra = [])
	{
		return Inertia::render('Admin/Edit', [
			'resource' => $resource,
			'id' => $id,
			'title' => __('admin.' . $resource) . ($id ? ' #' . $id : ' · ' . __('admin.create')),
			'fields' => $fields,
			'url' => '/admin/' . $resource . ($id ? '/' . $id : ''),
			'tables' => [],
			'links' => [],
			...$extra,
		]);
	}
}
