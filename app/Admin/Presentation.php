<?php

namespace App\Admin;

use App\Engine\Enums\PlanetType;
use App\Models\Planet;
use App\Models\User;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Presentation
{
	public static function field(string $key, string $type = 'text', array $options = [], mixed $value = null): array
	{
		return [
			'key' => $key,
			'label' => __('admin.' . $key),
			'type' => $type,
			'options' => $options,
			'value' => $value,
		];
	}

	public static function options(array $values): array
	{
		$result = [];

		foreach ($values as $value => $label) {
			$result[] = compact('value', 'label');
		}

		return $result;
	}

	public static function columns(array $keys): array
	{
		return array_map(fn(string $key) => ['key' => $key, 'label' => __('admin.' . $key)], $keys);
	}

	public static function link(mixed $label, ?string $url): array
	{
		return ['text' => $label ?? '—', 'url' => $url];
	}

	public static function value(mixed $value): mixed
	{
		if ($value instanceof DateTimeInterface) {
			return $value->format('d.m.Y H:i:s');
		}

		if ($value instanceof BackedEnum) {
			return method_exists($value, 'title') ? $value->title() : $value->value;
		}

		if ($value instanceof Arrayable) {
			$value = $value->toArray();
		}

		return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $value;
	}

	public static function record(Model $model, array $keys): array
	{
		$row = ['id' => $model->getAttribute('id')];

		foreach ($keys as $key) {
			$row[$key] = self::value($model->getAttribute($key));
		}

		return $row;
	}

	public static function fill(array $fields, ?Model $record): array
	{
		foreach ($fields as &$field) {
			if (!$record || in_array($field['key'], ['password', 'photo'], true)) {
				continue;
			}

			$key = $field['key'];
			$value = $record->getAttribute($key);

			$field['value'] = match (true) {
				in_array($key, ['roles', 'permissions'], true) => $value->pluck('id')->all(),
				$value instanceof BackedEnum => $value->value,
				$value instanceof DateTimeInterface => $value->format('Y-m-d\TH:i'),
				$field['type'] === 'checkbox' => (bool) $value,
				default => $value,
			};
		}
		return $fields;
	}

	public static function userLink(?User $user): array
	{
		return self::link(
			$user ? '[' . $user->id . '] ' . $user->username : null,
			$user && auth()->user()->can('users') ? '/admin/users/' . $user->id . '/edit' : null,
		);
	}

	public static function bodyLink(?Planet $planet): array
	{
		$resource = $planet?->planet_type === PlanetType::MOON ? 'moons' : 'planets';

		return self::link(
			$planet ? '[' . $planet->id . '] ' . $planet->name : null,
			$planet && auth()->user()->can($resource) ? '/admin/' . $resource . '/' . $planet->id . '/edit' : null,
		);
	}

	public static function table(string $title, array $columns, LengthAwarePaginator|Collection $rows): array
	{
		if ($rows instanceof Collection) {
			$rows = ['data' => $rows->values()->all(), 'total' => $rows->count()];
		}

		return compact('title', 'columns', 'rows');
	}
}
