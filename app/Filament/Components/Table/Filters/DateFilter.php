<?php

namespace App\Filament\Components\Table\Filters;

use App\Filament\Components\Form\Fields\Group;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class DateFilter extends BaseFilter
{
	protected string $column;
	protected string | Closure | null $displayFormat = 'M j, Y';
	protected CarbonInterface|string|Closure|null $startDate = null;
	protected CarbonInterface|string|Closure|null $endDate = null;

	protected function setUp(): void
	{
		parent::setUp();

		$this
			->useColumn($this->getName())
			->indicateUsing(function (array $state): array {
				$state = Arr::only($state, ['from', 'until']);

				if (!array_filter($state)) {
					return [];
				}

				/** @var string $displayFormat */
				$displayFormat = $this->evaluate($this->displayFormat);

				$labels = [
					'from' => __('From'),
					'until' => __('Until'),
				];

				$format = fn(string $field) => $state[$field] ?? null
					? [$labels[$field], Carbon::parse($state[$field])->format($displayFormat)] : [];

				$label = implode(' ', array_filter([
					...$format('from'),
					...$format('until'),
				]));

				return [$this->getIndicator() . ': ' . mb_strtolower($label)];
			});
	}

	public function apply(Builder $query, array $data = []): Builder
	{
		if ($this->hasQueryModificationCallback()) {
			return parent::apply($query, $data);
		}

		return $query
			->when(
				!empty($data['from']) && empty($data['until']),
				fn(Builder $query): Builder => $query->where($this->column, '>=', Carbon::parse($data['from'])->startOfDay()),
			)
			->when(
				empty($data['from']) && !empty($data['until']),
				fn(Builder $query): Builder => $query->where($this->column, '<=', Carbon::parse($data['until'])->endOfDay()),
			)
			->when(
				!empty($data['from']) && !empty($data['until']),
				fn(Builder $query): Builder => $query->whereBetween($this->column, [
					Carbon::parse($data['from'])->startOfDay(),
					Carbon::parse($data['until'])->endOfDay(),
				]),
			);
	}

	public function useColumn(string $column): self
	{
		$this->column = $column;

		return $this;
	}

	public function displayFormat(string|Closure|null $format): static
	{
		$this->displayFormat = $format;

		return $this;
	}

	public function startDate(CarbonInterface|string|Closure|null $date): static
	{
		$this->startDate = $date;

		return $this;
	}

	public function endDate(CarbonInterface|string|Closure|null $date): static
	{
		$this->endDate = $date;

		return $this;
	}

	public function getFormSchema(): array
	{
		return [
			Group::make($this->getLabel())
				->columns()
				->schema([
					DatePicker::make('from')
						->hiddenLabel()
						->native(false)
						->closeOnDateSelection()
						->weekStartsOnMonday()
						->placeholder(__('From'))
						->displayFormat($this->displayFormat)
						->format($this->displayFormat)
						->default($this->startDate)
						->afterStateHydrated(fn(DateTimePicker $component, $state) => $this->afterStateHydrated($component, $state)),
					DatePicker::make('until')
						->hiddenLabel()
						->native(false)
						->closeOnDateSelection()
						->weekStartsOnMonday()
						->placeholder(__('Until'))
						->displayFormat($this->displayFormat)
						->format($this->displayFormat)
						->default($this->endDate)
						->afterStateHydrated(fn(DateTimePicker $component, $state) => $this->afterStateHydrated($component, $state)),
				]),
		];
	}

	protected function afterStateHydrated(DateTimePicker $component, $state): void
	{
		if (blank($state)) {
			return;
		}

		if (!$state instanceof CarbonInterface) {
			try {
				$state = Carbon::createFromFormat($component->getFormat(), (string) $state, config('app.timezone'));
			} catch (InvalidFormatException) {
				try {
					$state = Carbon::parse($state, config('app.timezone'));
				} catch (InvalidFormatException) {
					$component->state(null);

					return;
				}
			}
		}

		$state = $state->setTimezone($component->getTimezone());

		if (!$component->isNative()) {
			$component->state($state->format('Y-m-d'));

			return;
		}

		if (!$component->hasTime()) {
			$component->state($state->toDateString());

			return;
		}

		$precision = $component->hasSeconds() ? 'second' : 'minute';

		if (!$component->hasDate()) {
			$component->state($state->toTimeString($precision));

			return;
		}

		$component->state($state->toDateTimeString($precision));
	}
}
