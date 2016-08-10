<?php

namespace Admin\Controllers;

use Admin\Controller;
use Friday\Core\Lang;
use Friday\Core\Models\Option;
use Friday\Core\Options;

/**
 * @RoutePrefix("/admin/options")
 * @Route("/")
 * @Private
 */
class OptionsController extends Controller
{
	const CODE = 'options';

	public function initialize()
	{
		parent::initialize();

		if (!$this->access->canReadController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$this->addToBreadcrumbs(Lang::getText('admin', 'page_title_index'), self::CODE);
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> self::CODE,
			'title' => 'Настройки',
			'icon'	=> 'settings',
			'sort'	=> 9999
		]];
	}

	public function indexAction ()
	{
		if ($this->request->isPost() && $this->request->hasPost('option'))
		{
			if (!$this->access->canWriteController(self::CODE, 'admin'))
				throw new \Exception('Access denied');

			$fields = $this->request->getPost('option');

			if (is_array($fields))
			{
				foreach ($fields as $field => $value)
				{
					$option = Option::findFirst(["conditions" => "name = :name:", "bind" => ["name" => trim($field)]]);

					if ($option)
						$option->update(['value' => trim($value)]);
				}
			}

			$this->cache->delete(Options::CACHE_KEY);

			return $this->response->redirect(self::CODE.'/');
		}

		$options = Option::find();

		$groups = [
			'general' => 'Основные',
			'xnova' => 'Игровые',
		];

		$this->view->setVar('options', $options);
		$this->view->setVar('groups', $groups);

		$this->tag->setTitle(Lang::getText('admin', 'page_title_index'));

		return true;
	}
}