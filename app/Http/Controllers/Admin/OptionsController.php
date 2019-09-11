<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;

class OptionsController extends AdminController
{
	public function initialize()
	{
		$this->addToBreadcrumbs(Lang::getText('admin', 'page_title_index'), self::CODE);
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'options',
			'title' => 'Настройки',
			'icon'	=> 'settings',
			'sort'	=> 9999
		]];
	}

	public function index ()
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

		View::share('title', __('admin.page_title_index'));

		return view('admin.options.index', ['options' => $options, 'groups' => $groups]);
	}
}