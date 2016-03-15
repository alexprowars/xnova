<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Helpers;
use App\Lang;
use App\Models\Message;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MessagesController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		Lang::includeLang('messages');
	}
	
	public function writeAction ()
	{
		$OwnerID = htmlspecialchars(addslashes($this->request->getQuery('id')));
	
		if (!$OwnerID)
			$this->message(_getText('mess_no_ownerid'), _getText('mess_error'));

		$OwnerRecord = $this->db->query("SELECT `id`, `username`, `galaxy`, `system`, `planet` FROM game_users WHERE ".(is_numeric($OwnerID) ? '`id`' : '`username`')." = '" . $OwnerID . "';")->fetch();

		if (!isset($OwnerRecord['id']))
			$this->message(_getText('mess_no_owner'), _getText('mess_error'));

		$msg = '';

		if (isset($_POST['text']))
		{
			$error = 0;

			if (!$_POST["text"])
			{
				$error++;
				$msg = "<div class=error>" . _getText('mess_no_text') . "</div>";
			}

			if (!$error && $this->user->message_block > time())
			{
				$error++;
				$msg = "<div class=error>" . _getText('mess_similar') . "</div>";
			}

			if ($this->user->lvl_minier == 1 && $this->user->lvl_raid)
			{
				$registerTime = $this->db->fetchColumn("SELECT create_time FROM game_users_info WHERE id = ".$this->user->id."");

				if ($registerTime > time() - 86400)
				{
					$lastSend = $this->db->fetchColumn("SELECT COUNT(*) as num FROM game_messages WHERE sender = " . $this->user->id . " AND time > ".(time() - (1 * 60))."");

					if ($lastSend > 0)
					{
						$error++;
						$msg = "<div class=error>" . _getText('mess_limit') . "</div>";
					}
				}
			}

			if (!$error)
			{
				$similar = $this->db->query("SELECT text FROM game_messages WHERE sender = " . $this->user->id . " AND time > ".(time() - (5 * 60))." ORDER BY time DESC LIMIT 1")->fetch();

				if (isset($similar['text']))
				{
					if (mb_strlen($similar['text'], 'UTF-8') < 1000)
					{
						similar_text($_POST["text"], $similar['text'], $sim);

						if ($sim > 80)
						{
							$error++;
							$msg = "<div class=error>" . _getText('mess_similar') . "</div>";
						}
					}
				}
			}

			if ($error == 0)
			{
				$msg = "<div class=success>" . _getText('mess_sended') . "</div>";

				$From = $this->user->username . " [" . $this->user->galaxy . ":" . $this->user->system . ":" . $this->user->planet . "]";
				$Message = Helpers::FormatText($_POST['text']);
				$Message = preg_replace('/[ ]+/',' ', $Message);
				$Message = strtr($Message, _getText('stopwords'));

				$this->game->sendMessage($OwnerRecord['id'], false, 0, 1, $From, $Message);
			}
		}

		$this->view->setVar('msg', $msg);
		$this->view->setVar('text', '');
		$this->view->setVar('id', $OwnerRecord['id']);
		$this->view->setVar('to', $OwnerRecord['username'] . " [" . $OwnerRecord['galaxy'] . ":" . $OwnerRecord['system'] . ":" . $OwnerRecord['planet'] . "]");

		if ($this->request->hasQuery('quote'))
		{
			$mes = Message::findFirst(['columns' => 'id, text', 'conditions' => 'id = ?0 AND (owner = ?1 OR sender = ?1)', 'bind' => [$this->request->getQuery('quote', 'int'), $this->user->id]]);

			if ($mes)
				$this->view->setVar('text', '[quote]' . preg_replace('/\<br(\s*)?\/?\>/iu', "", $mes->text) . '[/quote]');
		}

		$this->tag->setTitle('Сообщения');
		$this->showTopPanel(false);
	}

	public function delete ()
	{
		$items = $this->request->getPost('delete');

		if (!is_array($items) || !count($items))
			return false;

		$items = array_map('intval', $items);

		if (count($items))
			$this->db->updateAsDict('game_messages', ['deleted' => 1], ['conditions' => 'id IN ('.implode(',', $items).') AND owner = ?', 'bind' => [$this->user->id]]);

		return $this->response->redirect('messages/');
	}

	public function abuseAction ($id)
	{
		/**
		 * @var $mes \App\Models\Message
		 */
		$mes = Message::findFirst(['id = ?0 AND owner = ?1', 'bind' => [$id, $this->user->id]]);

		if ($mes)
		{
			$c = $this->db->query("SELECT `id` FROM game_users WHERE `authlevel` != 0");

			while ($cc = $c->fetch())
			{
				$this->game->sendMessage($cc['id'], $this->user->id, 0, 1, '<font color=red>' . $this->user->username . '</font>', 'От кого: ' . $mes->from . '<br>Дата отправления: ' . date("d-m-Y H:i:s", $mes->time) . '<br>Текст сообщения: ' . $mes->text);
			}

			$this->flashSession->message('alert', 'Жалоба отправлена администрации игры');
		}

		return $this->response->redirect('messages/');
	}
	
	public function indexAction ()
	{
		$parse = [];

		$parse['types'] = [0, 1, 2, 3, 4, 5, 15, 99, 100, 101];
		$parse['limit'] = [5, 10, 25, 50, 100, 200];

		$MessCategory = (!isset($_POST['messcat'])) ? (isset($_SESSION['m_cat']) ? $_SESSION['m_cat'] : 100) : intval($_POST['messcat']);
		$lim = (!isset($_POST['show_by']) || !in_array(intval($_POST['show_by']), $parse['limit'])) ? (isset($_SESSION['m_limit']) ? $_SESSION['m_limit'] : 10) : intval($_POST['show_by']);
		$start = $this->request->get('p', 'int', 0);

		if (!isset($_SESSION['m_limit']) || $_SESSION['m_limit'] != $lim)
			$_SESSION['m_limit'] = $lim;

		if (!isset($_SESSION['m_cat']) || $_SESSION['m_cat'] != $MessCategory)
			$_SESSION['m_cat'] = $MessCategory;

		if ($this->request->hasPost('delete'))
			return $this->delete();

		$parse['lim'] = $lim;
		$parse['category'] = $MessCategory;

		if ($this->user->messages > 0)
		{
			$this->user->messages = 0;
			$this->user->update();
		}

		if (!$start)
			$start = 1;

		$messages = $this->modelsManager->createBuilder()->from('App\Models\Message')->orderBy('time DESC');

		if ($MessCategory < 100)
			$messages->where('owner = ?0 AND type = ?1 AND deleted = ?2', [$this->user->id, $MessCategory, 0]);
		elseif ($MessCategory == 101)
		{
			$messages->columns(['m.id', 'm.type', 'm.time', 'm.text', 'sender' => 'm.owner', 'from' => 'CONCAT(u.username, \' [\', u.galaxy,\':\', u.system,\':\',u.planet, \']\')']);
			$messages->from(['m' => 'App\Models\Message', 'u' => 'App\Models\User']);
			$messages->where('u.id = m.owner AND m.sender = ?0', [$this->user->id]);
		}
		else
			$messages->where('owner = ?0 AND deleted = ?1', [$this->user->id, 0]);

		$paginator = new PaginatorQueryBuilder(
		[
			"builder"  	=> $messages,
			"limit" 	=> $lim,
			"page"  	=> $start
		]);

		$page = $paginator->getPaginate();

		$parse['pages'] = Helpers::pagination($page->total_items, $lim, '/messages/', $page->current);

		$this->view->setVar('parse', $parse);
		$this->view->setVar('page', $page);

		$this->tag->setTitle('Сообщения');
		$this->showTopPanel(false);

		return true;
	}
}