<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Models\Note;
use Illuminate\Http\Request;

class NotesController extends Controller
{
	public function new(Request $request)
	{
		$priority = (int) $request->post('u', 0);

		$title = $request->post('title', '');
		$text = $request->post('text', '');

		if ($title == '') {
			$title = __('notes.NoTitle');
		}

		if ($text == '') {
			$text = __('notes.NoText');
		}

		$note = new Note();
		$note->user_id = $this->user->id;
		$note->priority = $priority;
		$note->title = $title;
		$note->text = $text;
		$note->save();

		throw new RedirectException('/notes/edit/' . $note->id, __('notes.NoteAdded'));
	}

	public function edit(Request $request, int $noteId)
	{
		$note = Note::query()->where('user_id', $this->user->id)
			->where('id', $noteId)->first();

		if (!$note) {
			throw new Exception(__('notes.notpossiblethisway'));
		}

		if ($request->isMethod('post')) {
			$priority = (int) $request->post('u', 0);

			$title = $request->post('title', '');
			$text = $request->post('text', '');

			if ($title == '') {
				$title = __('notes.NoTitle');
			}

			if ($text == '') {
				$text = __('notes.NoText');
			}

			$note->priority = $priority;
			$note->title = $title;
			$note->text = $text;
			$note->save();

			throw new RedirectException('/notes/edit/' . $note->id, __('notes.NoteUpdated'));
		}

		$parse = [
			'id' => (int) $note->id,
			'priority' => (int) $note->priority,
			'title' => $note->title,
			'text' => str_replace(["\n", "\r", "\n\r"], '<br>', stripslashes($note->text)),
		];

		return response()->state($parse);
	}

	public function index(Request $request)
	{
		if ($request->isMethod('post')) {
			$deleteIds = array_map('intval', $request->post('delete'));

			if (is_array($deleteIds) && count($deleteIds)) {
				Note::query()->where('user_id', $this->user->id)
					->whereIn('id', $deleteIds)->delete();
			}

			throw new RedirectException('/notes', __('notes.NoteDeleteds'));
		}

		$notes = Note::query()->where('user_id', $this->user->id)
			->orderByDesc('updated_at')->get();

		$parse = [];
		$parse['items'] = [];

		foreach ($notes as $note) {
			$list = [];

			if ($note->priority == 0) {
				$list['color'] = 'lime';
			} elseif ($note->priority == 1) {
				$list['color'] = 'yellow';
			} elseif ($note->priority == 2) {
				$list['color'] = 'red';
			}

			$list['id'] = $note->id;
			$list['time'] = $note->updated_at?->utc()->toAtomString();
			$list['title'] = $note->title;

			$parse['items'][] = $list;
		}

		return response()->state($parse);
	}
}
