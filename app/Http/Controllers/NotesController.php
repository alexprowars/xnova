<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class NotesController extends Controller
{
	public function index(): array
	{
		$notes = $this->user->notes()
			->latest('updated_at')->get();

		$items = [];

		foreach ($notes as $note) {
			$items[] = [
				'id' => $note->id,
				'date' => $note->updated_at?->utc()->toAtomString(),
				'title' => $note->title,
				'color' => match ($note->priority) {
					0 => 'lime',
					1 => 'yellow',
					2 => 'red',
					default => null,
				},
			];
		}

		return $items;
	}

	public function delete(Request $request): void
	{
		$deleteIds = array_map('intval', Arr::wrap($request->post('id', [])));

		if (!empty($deleteIds)) {
			$this->user->notes()->whereKey($deleteIds)->delete();
		}
	}

	public function create(Request $request): array
	{
		$priority = (int) $request->post('priority', 0);

		$title = $request->post('title');
		$message = $request->post('message');

		if (empty($title)) {
			$title = __('notes.no_title');
		}

		if (empty($message)) {
			$message = __('notes.no_text');
		}

		$note = $this->user->notes()->make();
		$note->priority = $priority;
		$note->title = $title;
		$note->text = $message;
		$note->save();

		return [
			'id' => $note->id,
		];
	}

	public function edit(int $id): array
	{
		$note = $this->user->notes()->findOne($id);

		if (!$note) {
			throw new Exception(__('notes.not_found'));
		}

		return [
			'id' => $note->id,
			'priority' => (int) $note->priority,
			'title' => $note->title,
			'text' => str_replace(["\n", "\r", "\n\r"], '<br>', stripslashes($note->text)),
		];
	}

	public function update(int $id, Request $request): void
	{
		$note = $this->user->notes()->findOne($id);

		if (!$note) {
			throw new Exception(__('notes.not_found'));
		}

		$priority = (int) $request->post('priority', 0);

		$title = $request->post('title');
		$message = $request->post('message');

		if (empty($title)) {
			$title = __('notes.no_title');
		}

		if (empty($message)) {
			$message = __('notes.no_text');
		}

		$note->priority = $priority;
		$note->title = $title;
		$note->text = $message;
		$note->save();
	}
}
