<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\Note;
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
			$item = [
				'id' => $note->id,
				'time' => $note->updated_at?->utc()->toAtomString(),
				'title' => $note->title,
				'color' => '',
			];

			if ($note->priority == 0) {
				$item['color'] = 'lime';
			} elseif ($note->priority == 1) {
				$item['color'] = 'yellow';
			} elseif ($note->priority == 2) {
				$item['color'] = 'red';
			}

			$items[] = $item;
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
			$title = __('notes.NoTitle');
		}

		if (empty($message)) {
			$message = __('notes.NoText');
		}

		$note = new Note();
		$note->user()->associate($this->user);
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
			throw new Exception('Заметка не найдена');
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
			throw new Exception('Заметка не найдена');
		}

		$priority = (int) $request->post('priority', 0);

		$title = $request->post('title');
		$message = $request->post('message');

		if (empty($title)) {
			$title = __('notes.NoTitle');
		}

		if (empty($message)) {
			$message = __('notes.NoText');
		}

		$note->priority = $priority;
		$note->title = $title;
		$note->text = $message;
		$note->save();
	}
}
