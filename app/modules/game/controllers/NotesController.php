<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Lang;

class NotesController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		Lang::includeLang('notes');
	}

	public function newAction ()
	{
		if ($this->request->isPost())
		{
			$priority = $this->request->getPost('u', 'int', 0);

			$title 	= $this->request->getPost('title', 'string', '') ? $this->request->getPost('title', 'string') : _getText('NoTitle');
			$text 	= $this->request->getPost('text', 'string', '') ? $this->request->getPost('text', 'string') : _getText('NoText');

			$this->db->insertAsDict('game_notes',
			[
				'owner' 	=> $this->user->id,
				'time' 		=> time(),
				'priority' 	=> $priority,
				'title' 	=> $title,
				'text' 		=> $text
			]);

			$id = $this->db->lastInsertId();

			$this->message(_getText('NoteAdded'), _getText('Please_Wait'), '/notes/edit/'.$id.'/', 1);
		}

		$this->tag->setTitle('Создание заметки');
		$this->showTopPanel(false);
	}

	public function editAction ($noteId)
	{
		$parse = $this->db->query("SELECT * FROM game_notes WHERE owner = ".$this->user->id." AND id = ".intval($noteId)."")->fetch();

		if (!$parse['id'])
			$this->message(_getText('notpossiblethisway'), _getText('Error'));

		if ($this->request->isPost())
		{
			$priority = $this->request->getPost('u', 'int', 0);

			$title 	= $this->request->getPost('title', 'string', '') ? $this->request->getPost('title', 'string') : _getText('NoTitle');
			$text 	= $this->request->getPost('text', 'string', '') ? $this->request->getPost('text', 'string') : _getText('NoText');

			$this->db->updateAsDict('game_notes',
			[
				'time' 		=> time(),
				'priority' 	=> $priority,
				'title' 	=> $title,
				'text' 		=> $text
			], "id = ".$parse['id']);

			$this->message(_getText('NoteUpdated'), _getText('Please_Wait'), '/notes/edit/'.$parse['id'].'/', 1);
		}

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle(_getText('Notes'));
		$this->showTopPanel(false);
	}
	
	public function indexAction ()
	{
		if ($this->request->isPost())
		{
			$deleted = 0;

			foreach ($_POST as $a => $b)
			{
				if (preg_match("/delmes/iu", $a) && $b == "y")
				{
					$id = intval(trim(str_replace("delmes", "", $a)));

					$note_query = $this->db->query("SELECT id FROM game_notes WHERE id = ".$id." AND owner = ".$this->user->id."")->fetch();

					if (isset($note_query['id']))
					{
						$deleted++;
						$this->db->query("DELETE FROM game_notes WHERE `id` = ".$note_query['id']."");
					}
				}
			}

			if ($deleted)
			{
				$mes = ($deleted == 1) ? _getText('NoteDeleted') : _getText('NoteDeleteds');
				$this->message($mes, _getText('Please_Wait'), '/notes/', 3);
			}
			else
				$this->response->redirect("notes/");
		}

		$notes = $this->db->query("SELECT * FROM game_notes WHERE owner = ".$this->user->id." ORDER BY time DESC");

		$parse = [];
		$parse['list'] = [];

		while ($note = $notes->fetch())
		{
			$list = [];

			if ($note["priority"] == 0)
				$list['color'] = "lime";
			elseif ($note["priority"] == 1)
				$list['color'] = "yellow";
			elseif ($note["priority"] == 2)
				$list['color'] = "red";

			$list['id'] = $note['id'];
			$list['time'] = $this->game->datezone("Y-m-d h:i:s", $note["time"]);
			$list['title'] = $note['title'];
			$list['text'] = mb_strlen($note['text'], 'UTF-8');

			$parse['list'][] = $list;
		}

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Заметки');
		$this->showTopPanel(false);
	}
}