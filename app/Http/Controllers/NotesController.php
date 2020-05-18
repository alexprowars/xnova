<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Http\Request;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Game;
use Xnova\Models\Note;

/** @noinspection PhpUnused */
class NotesController extends Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->showTopPanel(false);
	}

	public function new(Request $request)
	{
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

			$note = new Note();

			$note->user_id = $this->user->id;
			$note->priority = $priority;
			$note->title = $title;
			$note->text = $text;

			$note->save();

			throw new RedirectException(__('notes.NoteAdded'), '/notes/edit/' . $note->id . '/');
		}

		$this->setTitle('Создание заметки');

		return [];
	}

	public function edit(Request $request, int $noteId)
	{
		$note = Note::query()->where('user_id', $this->user->id)
			->where('id', (int) $noteId)->first();

		if (!$note) {
			throw new ErrorException(__('notes.notpossiblethisway'));
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

			$note->time = time();
			$note->priority = $priority;
			$note->title = $title;
			$note->text = $text;

			$note->update();

			throw new RedirectException(__('notes.NoteUpdated'), '/notes/edit/' . $note->id . '/');
		}

		$parse = [
			'id' => (int) $note->id,
			'priority' => (int) $note->priority,
			'title' => $note->title,
			'text' => str_replace(["\n", "\r", "\n\r"], '<br>', stripslashes($note->text)),
		];

		$this->setTitle(__('notes.Notes'));

		return $parse;
	}

	public function index(Request $request)
	{
		if ($request->isMethod('post')) {
			$deleteIds = array_map('int', $request->post('delete'));

			if (is_array($deleteIds) && count($deleteIds)) {
				Note::query()->where('user_id', $this->user->id)
					->whereIn('id', $deleteIds)->delete();
			}

			throw new RedirectException(__('notes.NoteDeleteds'), '/notes/');
		}

		$notes = Note::query()->where('user_id', $this->user->id)
			->orderByDesc('time')->get();

		$parse = [];
		$parse['items'] = [];

		foreach ($notes as $note) {
			$list = [];

			if ($note->priority == 0) {
				$list['color'] = "lime";
			} elseif ($note->priority == 1) {
				$list['color'] = "yellow";
			} elseif ($note->priority == 2) {
				$list['color'] = "red";
			}

			$list['id'] = (int) $note->id;
			$list['time'] = Game::datezone("Y.m.d h:i:s", $note->time);
			$list['title'] = $note->title;

			$parse['items'][] = $list;
		}

		$this->setTitle('Заметки');

		return $parse;
	}
}
