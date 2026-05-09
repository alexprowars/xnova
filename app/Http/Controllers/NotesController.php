<?php

namespace App\Http\Controllers;

use App\Exceptions\PageException;
use App\Support\ToastType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class NotesController extends Controller
{
	public function index()
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

		return Inertia::render('Notes/List', [
			'items' => $items,
		]);
	}

	public function create()
	{
		return Inertia::render('Notes/Create');
	}

	public function delete(Request $request): void
	{
		$deleteIds = array_map('intval', Arr::wrap($request->post('id', [])));

		if (!empty($deleteIds)) {
			$this->user->notes()->whereKey($deleteIds)->delete();
		}
	}

	public function store(Request $request)
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

		toast(ToastType::SUCCESS, 'Заметка добавлена');

		return to_route('notes.detail', ['id' => $note->id]);
	}

	public function edit(int $id)
	{
		$note = $this->user->notes()->findOne($id);

		if (!$note) {
			throw new PageException(__('notes.not_found'));
		}

		$result = [
			'id' => $note->id,
			'priority' => (int) $note->priority,
			'title' => $note->title,
			'message' => str_replace(["\n", "\r", "\n\r"], '<br>', stripslashes($note->text)),
		];

		return Inertia::render('Notes/Edit', [
			'item' => $result,
		]);
	}

	public function update(int $id, Request $request)
	{
		$note = $this->user->notes()->findOne($id);

		if (!$note) {
			throw new PageException(__('notes.not_found'));
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

		toast(ToastType::SUCCESS, 'Заметка обновлена');

		return back();
	}
}
