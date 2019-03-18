<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\Note;
use Xnova\Request;

/**
 * @RoutePrefix("/notes")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class NotesController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		Lang::includeLang('notes', 'xnova');
	}

	public function newAction ()
	{
		if ($this->request->isPost())
		{
			$priority = (int) $this->request->getPost('u', 'int', 0);

			$title = $this->request->getPost('title', 'string', '');
			$text = $this->request->getPost('text', 'string', '');

			if ($title == '')
				$title = _getText('NoTitle');

			if ($text == '')
				$text = _getText('NoText');

			$note = new Note();

			$note->user_id = $this->user->id;
			$note->priority = $priority;
			$note->title = $title;
			$note->text = $text;

			$note->create();

			throw new RedirectException(_getText('NoteAdded'), '/notes/edit/'.$note->id.'/');
		}

		$this->tag->setTitle('Создание заметки');
	}

	public function editAction ($noteId = 0)
	{
		$note = Note::findFirst([
			'conditions' => 'user_id = :user: AND id = :id:',
			'bind' => [
				'user' => $this->user->id,
				'id' => (int) $noteId
			]
		]);

		if (!$note)
			throw new ErrorException(_getText('notpossiblethisway'));

		if ($this->request->isPost())
		{
			$priority = (int) $this->request->getPost('u', 'int', 0);

			$title = $this->request->getPost('title', 'string', '');
			$text = $this->request->getPost('text', 'string', '');

			if ($title == '')
				$title = _getText('NoTitle');

			if ($text == '')
				$text = _getText('NoText');

			$note->time = time();
			$note->priority = $priority;
			$note->title = $title;
			$note->text = $text;

			$note->update();

			throw new RedirectException(_getText('NoteUpdated'), '/notes/edit/'.$note->id.'/');
		}

		$parse = [
			'id' => (int) $note->id,
			'priority' => (int) $note->priority,
			'title' => $note->title,
			'text' => str_replace(["\n", "\r", "\n\r"], '<br>', stripslashes($note->text)),
		];

		Request::addData('page', $parse);

		$this->tag->setTitle(_getText('Notes'));
	}
	
	public function indexAction ()
	{
		if ($this->request->isPost())
		{
			$deleteIds = $this->request->getPost('delete');

			if (!is_array($deleteIds))
				$deleteIds = [];

			foreach ($deleteIds as $id)
			{
				$note = Note::findFirst([
					'conditions' => 'user_id = :user: AND id = :id:',
					'bind' => [
						'user' => $this->user->id,
						'id' => (int) $id
					]
				]);

				if ($note)
					$note->delete();
			}

			throw new RedirectException(_getText('NoteDeleteds'), '/notes/');
		}

		$notes = Note::find([
			'conditions' => 'user_id = :user:',
			'bind' => [
				'user' => $this->user->id
			],
			'order' => 'time DESC'
		]);

		$parse = [];
		$parse['items'] = [];

		foreach ($notes as $note)
		{
			$list = [];

			if ($note->priority == 0)
				$list['color'] = "lime";
			elseif ($note->priority == 1)
				$list['color'] = "yellow";
			elseif ($note->priority == 2)
				$list['color'] = "red";

			$list['id'] = (int) $note->id;
			$list['time'] = $this->game->datezone("Y.m.d h:i:s", $note->time);
			$list['title'] = $note->title;

			$parse['items'][] = $list;
		}

		Request::addData('page', $parse);

		$this->tag->setTitle('Заметки');
	}
}