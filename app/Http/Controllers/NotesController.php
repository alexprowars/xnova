<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\Note;
use Illuminate\Http\Request;

class NotesController extends Controller
{
	public function index()
	{
		$notes = Note::query()->where('user_id', $this->user->id)
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

		return response()->state($items);
	}

	public function delete(Request $request)
	{
		$deleteIds = $request->post('id', []);

		if (!empty($deleteIds) && is_array($deleteIds)) {
			Note::query()->where('user_id', $this->user->id)
				->whereIn('id', array_map('intval', $deleteIds))->delete();
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
		$note = Note::query()->where('user_id', $this->user->id)
			->where('id', $id)->first();

		if (!$note) {
			throw new Exception('Заметка не найдена');
		}

		$parse = [
			'id' => (int) $note->id,
			'priority' => (int) $note->priority,
			'title' => $note->title,
			'text' => str_replace(["\n", "\r", "\n\r"], '<br>', stripslashes($note->text)),
		];

		return response()->state($parse);
	}

	public function update(int $id, Request $request)
	{
		$note = Note::query()->where('user_id', $this->user->id)
			->where('id', $id)->first();

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
