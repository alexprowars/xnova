<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Format;
use Xnova\Helpers;
use Friday\Core\Lang;
use Xnova\Models\Message;
use Xnova\Request;
use Xnova\User;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Xnova\Controller;

/**
 * @RoutePrefix("/messages")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class MessagesController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('messages', 'xnova');
	}

	public function writeAction ($userId = 0)
	{
		if (!$userId)
			throw new ErrorException(_getText('mess_no_ownerid'), _getText('mess_error'));

		$OwnerRecord = $this->db->query("SELECT `id`, `username`, `galaxy`, `system`, `planet` FROM game_users WHERE ".(is_numeric($userId) ? '`id`' : '`username`')." = '" . $userId . "';")->fetch();

		if (!isset($OwnerRecord['id']))
			throw new ErrorException(_getText('mess_no_owner'), _getText('mess_error'));

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
					$lastSend = $this->db->fetchColumn("SELECT COUNT(*) as num FROM game_messages WHERE user_id = " . $this->user->id . " AND time > ".(time() - (1 * 60))."");

					if ($lastSend > 0)
					{
						$error++;
						$msg = "<div class=error>" . _getText('mess_limit') . "</div>";
					}
				}
			}

			if (!$error)
			{
				$similar = $this->db->query("SELECT text FROM game_messages WHERE user_id = " . $this->user->id . " AND time > ".(time() - (5 * 60))." ORDER BY time DESC LIMIT 1")->fetch();

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
				$Message = Format::text($_POST['text']);
				$Message = preg_replace('/[ ]+/',' ', $Message);
				$Message = strtr($Message, _getText('stopwords'));

				User::sendMessage($OwnerRecord['id'], false, 0, 1, $From, $Message);
			}
		}

		$this->view->setVar('msg', $msg);
		$this->view->setVar('text', '');
		$this->view->setVar('id', $OwnerRecord['id']);
		$this->view->setVar('to', $OwnerRecord['username'] . " [" . $OwnerRecord['galaxy'] . ":" . $OwnerRecord['system'] . ":" . $OwnerRecord['planet'] . "]");

		if ($this->request->hasQuery('quote'))
		{
			$mes = Message::findFirst(['columns' => 'id, text', 'conditions' => 'id = ?0 AND (user_id = ?1 OR from_id = ?1)', 'bind' => [$this->request->getQuery('quote', 'int'), $this->user->id]]);

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

		return true;
	}

	public function abuseAction ($id)
	{
		$mes = Message::findFirst(['id = ?0 AND user_id = ?1', 'bind' => [$id, $this->user->id]]);

		if ($mes)
		{
			$c = $this->db->query("SELECT `id` FROM game_users WHERE `authlevel` != 0");

			while ($cc = $c->fetch())
			{
				User::sendMessage($cc['id'], $this->user->id, 0, 1, '<font color=red>' . $this->user->username . '</font>', 'От кого: ' . $mes->from . '<br>Дата отправления: ' . date("d-m-Y H:i:s", $mes->time) . '<br>Текст сообщения: ' . $mes->text);
			}

			$this->flashSession->message('alert', 'Жалоба отправлена администрации игры');
		}

		return $this->response->redirect('messages/');
	}
	
	public function indexAction ()
	{
		$parse = [];

		$types = [0, 1, 2, 3, 4, 5, 15, 99, 100, 101];
		$limits = [5, 10, 25, 50, 100, 200];

		$category = 100;

		if ($this->session->has('m_cat'))
			$category = (int) $this->session->get('m_cat');

		if ($this->request->hasPost('category'))
			$category = (int) $this->request->getPost('category', 'int', 100);

		if (!in_array($category, $types))
			$category = 100;

		$limit = 10;

		if ($this->session->has('m_limit'))
			$limit = (int) $this->session->get('m_limit');

		if ($this->request->hasPost('limit'))
			$limit = (int) $this->request->getPost('limit', 'int', 10);

		if (!in_array($limit, $limits))
			$limit = 10;

		$page = (int) $this->request->get('p', 'int', 0);

		if (!$page)
			$page = 1;

		if (!$this->session->has('m_limit') || $this->session->get('m_limit') != $limit)
			$this->session->set('m_limit', $limit);

		if (!$this->session->has('m_cat') || $this->session->get('m_cat') != $category)
			$this->session->set('m_cat', $category);

		if ($this->request->hasPost('delete'))
			$this->delete();

		$parse['limit'] = $limit;
		$parse['category'] = $category;

		if ($this->user->messages > 0)
		{
			$this->user->messages = 0;
			$this->user->update();
		}

		$messages = $this->modelsManager->createBuilder()->from(['m' => 'Xnova\Models\Message'])->orderBy('m.time DESC');
		$messages->columns(['m.id', 'm.type', 'm.time', 'm.text', 'm.from_id', 'm.theme']);

		if ($category < 100)
			$messages->where('m.user_id = ?0 AND m.type = ?1 AND m.deleted = ?2', [$this->user->id, $category, 0]);
		elseif ($category == 101)
		{
			$messages->columns(['m.id', 'm.type', 'm.time', 'm.text', 'from_id' => 'm.user_id', 'theme' => 'CONCAT(u.username, \' [\', u.galaxy,\':\', u.system,\':\',u.planet, \']\')']);
			$messages->addFrom('Xnova\Models\User', 'u');
			$messages->where('u.id = m.user_id AND m.from_id = ?0', [$this->user->id]);
		}
		else
			$messages->where('m.user_id = ?0 AND m.deleted = ?1', [$this->user->id, 0]);

		$paginator = new PaginatorQueryBuilder([
			"builder"  	=> $messages,
			"limit" 	=> $limit,
			"page"  	=> $page
		]);

		$page = $paginator->getPaginate();

		$items = $page->items->toArray();
		$parse['items'] = [];

		foreach ($items as $item)
		{
			$parse['items'][] = [
				'id' => (int) $item['id'],
				'type' => (int) $item['type'],
				'time' => (int) $item['time'],
				'from' => (int) $item['from_id'],
				'theme' => $item['theme'],
				'text' => str_replace(["\r\n", "\n", "\r"], '<br>', stripslashes(str_replace('#BASEPATH#', $this->url->getBaseUri(), $item['text']))),
			];
		}

		$parse['pagination'] = [
			'total' => (int) $page->total_items,
			'limit' => (int) $limit,
			'page' => (int) $page->current
		];

		$parse['parser'] = $this->user->getUserOption('bb_parser') > 0;

		Request::addData('page', $parse);

		$this->tag->setTitle('Сообщения');
		$this->showTopPanel(false);
	}
}