<?php

namespace App\Filament\Widgets;

use App\Format;
use App\Helpers;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class OnlineList extends BaseWidget
{
	protected int | string | array $columnSpan = 'full';
	protected static bool $isLazy = false;

	public function table(Table $table): Table
	{
		return $table
			->query(User::query())
			->modifyQueryUsing(fn (Builder $query) => $query->where('onlinetime', '>', now()->subMinutes(15)))
			->defaultPaginationPageOption(10)
			->defaultSort('ip')
			->heading('Активные игроки')
			->emptyStateHeading('Нет активных игроков')
			->poll('60s')
			->searchable()
			->striped()
			->columns([
				Tables\Columns\IconColumn::make('id')
					->label('')
					->icon('heroicon-o-envelope')
					->url(fn(User $record) => url('messages/write/' . $record->id . '/')),
				Tables\Columns\TextColumn::make('username')
					->label('Логин игрока')
					->sortable(),
				Tables\Columns\TextColumn::make('ip')
					->label('Ip')
					->formatStateUsing(fn($state) => Helpers::convertIp($state))
					->sortable(),
				Tables\Columns\TextColumn::make('alliance_name')
					->label('Альянс')
					->sortable(),
				Tables\Columns\TextColumn::make('onlinetime')
					->label('Активность')
					->formatStateUsing(fn (User $record) => Format::time($record->onlinetime->diffInSeconds(now())))
					->sortable(),
			]);
	}

	protected function paginateTableQuery(Builder $query): Paginator | CursorPaginator
	{
		return $query->paginate(($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage());
	}
}
