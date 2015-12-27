<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\request;
use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;

class NotesController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
		
		strings::includeLang('notes');
	}
	
	public function show ()
	{
		$n = @intval($_GET['n']);
		
		if (isset($_POST["s"]) && ($_POST["s"] == 1 || $_POST["s"] == 2))
		{
			$time = time();
			$priority = $_POST["u"];
			$title = ($_POST["title"]) ? db::escape_string(strip_tags($_POST["title"])) : _getText('NoTitle');
			$text = ($_POST["text"]) ? db::escape_string(strip_tags($_POST["text"])) : _getText('NoText');
		
			if ($_POST["s"] == 1)
			{
				db::query("INSERT INTO game_notes SET owner=".user::get()->data['id'].", time=$time, priority=$priority, title='$title', text='$text'");
				$this->message(_getText('NoteAdded'), _getText('Please_Wait'), '?set=notes', "3");
			}
			elseif ($_POST["s"] == 2)
			{
				$id = intval($_POST["n"]);
				$note_query = db::query("SELECT * FROM game_notes WHERE id=$id AND owner=" . user::get()->data["id"]);
		
				if (!$note_query)
					$this->message(_getText('notpossiblethisway'), _getText('Notes'));

				db::query("UPDATE game_notes SET time=$time, priority=$priority, title='$title', text='$text' WHERE id=$id");
				$this->message(_getText('NoteUpdated'), _getText('Please_Wait'), '?set=notes', "3");
			}
		
		}
		elseif ($_POST)
		{
			$deleted = 0;
		
			foreach ($_POST as $a => $b)
			{
				if (preg_match("/delmes/iu", $a) && $b == "y")
				{
					$id = str_replace("delmes", "", $a);
					$note_query = db::query("SELECT * FROM game_notes WHERE id=$id AND owner=".user::get()->data['id']."");
					//comprobamos,
					if ($note_query)
					{
						$deleted++;
						db::query("DELETE FROM game_notes WHERE `id`=$id;"); // y borramos
					}
				}
			}
			if ($deleted)
			{
				$mes = ($deleted == 1) ? _getText('NoteDeleted') : _getText('NoteDeleteds');
				$this->message($mes, _getText('Please_Wait'), '?set=notes', "3");
			}
			else
				request::redirectTo("?set=notes");
		}
		else
		{
			if (isset($_GET["a"]) && $_GET["a"] == 1)
			{
				$parse = array();
		
				$parse['c_Options'] = "<option value=2 selected=selected>"._getText('Important')."</option>
					  <option value=1>"._getText('Normal')."</option>
					  <option value=0>"._getText('Unimportant')."</option>";
		
				$parse['cntChars'] = '0';
				$parse['text'] = '';
				$parse['title'] = '';
				$parse['TITLE'] = _getText('Createnote');
				$parse['inputs'] = '<input type=hidden name=s value=1>';
		
				$this->setTemplate('notes_form');
				$this->set('parse', $parse);

				$this->setTitle('Создание заметки');
				$this->showTopPanel(false);
				$this->display();
		
			}
			elseif (isset($_GET["a"]) && $_GET["a"] == 2)
			{
				$parse = db::query("SELECT * FROM game_notes WHERE owner=".user::get()->data['id']." AND id=$n", true);
		
				if (!$parse['id'])
					$this->message(_getText('notpossiblethisway'), _getText('Error'));
		
				$cntChars = mb_strlen($parse['text'], 'UTF-8');
		
				$SELECTED[0] = '';
				$SELECTED[1] = '';
				$SELECTED[2] = '';
				$SELECTED[$parse['priority']] = ' selected="selected"';
		
				$parse['c_Options'] = "<option value=2{$SELECTED[2]}>"._getText('Important')."</option>
					  <option value=1{$SELECTED[1]}>"._getText('Normal')."</option>
					  <option value=0{$SELECTED[0]}>"._getText('Unimportant')."</option>";
		
				$parse['cntChars'] = $cntChars;
				$parse['TITLE'] = _getText('Editnote');
				$parse['inputs'] = '<input type=hidden name=s value=2><input type=hidden name=n value=' . $parse['id'] . '>
									<table width=651><tr><td class=c>Просмотр заметки</td></tr><tr><th style="text-align:left;font-weight:normal;">
									<span id="um' . $parse['id'] . '" style="display:none;"></span><span id="m' . $parse['id'] . '"></span><script>Text(\'' . str_replace(array("\n", "\r", "\n\r"), '<br>', addslashes($parse['text'])) . '\', \'m' . $parse['id'] . '\');</script>
									</th></tr></table><div class="separator"></div>';
		
				$this->setTemplate('notes_form');
				$this->set('parse', $parse);

				$this->setTitle(_getText('Notes'));
				$this->showTopPanel(false);
				$this->display();
			}
			else
			{
				$notes_query = db::query("SELECT * FROM game_notes WHERE owner=".user::get()->data['id']." ORDER BY time DESC");
				$parse = array();
				$parse['list'] = array();
		
				while ($note = db::fetch($notes_query))
				{
					$list = array();
		
					if ($note["priority"] == 0)
					{
						$list['NOTE_COLOR'] = "lime";
					} //Importante
					elseif ($note["priority"] == 1)
					{
						$list['NOTE_COLOR'] = "yellow";
					} //Normal
					elseif ($note["priority"] == 2)
					{
						$list['NOTE_COLOR'] = "red";
					}
					//Sin importancia
		
					$list['NOTE_ID'] = $note['id'];
					$list['NOTE_TIME'] = datezone("Y-m-d h:i:s", $note["time"]);
					$list['NOTE_TITLE'] = $note['title'];
					$list['NOTE_TEXT'] = mb_strlen($note['text'], 'UTF-8');
		
					$parse['list'][] = $list;
				}
		
				$this->setTemplate('notes');
				$this->set('parse', $parse);

				$this->setTitle('Заметки');
				$this->showTopPanel(false);
				$this->display();
			}
		}
	}
}

?>