<?php

namespace App\Filament\Widgets;

use App\Format;
use App\Helpers;
use App\Models\User;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class OnlineList extends TableWidget
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
				IconColumn::make('id')
					->label(new HtmlString('&nbsp;'))
					->icon('heroicon-o-envelope')
					->url(fn(User $record) => url('messages/write/' . $record->id . '/')),
				TextColumn::make('username')
					->label('Логин игрока')
					->sortable(),
				TextColumn::make('ip')
					->label('Ip')
					->formatStateUsing(fn($state) => Helpers::convertIp($state))
					->sortable(),
				TextColumn::make('alliance_name')
					->label('Альянс')
					->sortable(),
				TextColumn::make('onlinetime')
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
