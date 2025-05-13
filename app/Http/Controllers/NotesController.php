<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class NotesController extends Controller
{
	public function index()
	{
		$notes = Note::query()->whereBelongsTo($this->user)
			->orderByDesc('updated_at')->get();

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

	public function delete(Request $request)
	{
		$deleteIds = array_map('intval', Arr::wrap($request->post('id', [])));

		if (!empty($deleteIds)) {
			Note::query()->whereBelongsTo($this->user)
				->whereKey($deleteIds)->delete();
		}
	}

	public function create(Request $request)
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
		$note->user_id = $this->user->id;
		$note->priority = $priority;
		$note->title = $title;
		$note->text = $message;
		$note->save();

		return [
			'id' => $note->id,
		];
	}

	public function edit(int $id)
	{
		$note = Note::query()->whereBelongsTo($this->user)
			->findOne($id);

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

	public function update(int $id, Request $request)
	{
		$note = Note::query()->whereBelongsTo($this->user)
			->findOne($id);

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
