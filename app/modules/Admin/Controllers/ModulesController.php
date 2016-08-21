<?php

namespace Admin\Controllers;

use Admin\Controller;
use Admin\Forms\ModuleForm;
use Friday\Core\Lang;
use Friday\Core\Models\Module;
use Friday\Core\Helpers\Cache as CacheHelper;

/**
 * @RoutePrefix("/admin/modules")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ModulesController extends Controller
{
	const CODE = 'modules';

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
			'title' => 'Модули',
			'icon'	=> 'grid',
			'sort'	=> 1020
		]];
	}

	public function indexAction ()
	{
		$modules = Module::find(['order' => 'sort ASC']);

		foreach ($modules as $module)
			Lang::includeLang('main', $module->code);

		$this->view->setVar('modules', $modules);
		$this->tag->setTitle(Lang::getText('admin', 'page_title_index'));
	}

	public function editAction ($moduleId)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$module = Module::findFirst($moduleId);

		if (!$module)
		{
			$this->flashSession->error('Модуль не найден');

			return $this->response->redirect(self::CODE.'/');
		}

		$form = new ModuleForm($module);
		$form->setAction($this->url->get(self::CODE.'/eidt/'.$module->id.'/'));

		if ($this->request->isPost())
		{
			if ($form->isValid($this->request->getPost()))
			{
				if ($module->update())
				{
					$this->flashSession->success('Изменения сохранены');

					return $this->response->redirect(self::CODE.'/edit/'.$module->id.'/');
				}
				else
					$this->flashSession->error('Произошла ошибка при сохранении');
			}
			else
			{
				foreach ($form->getMessages() as $message)
				{
					$this->flashSession->error($message);
				}
			}
		}

		$this->view->setVar('form', $form);

		$this->addToBreadcrumbs('Редактирование модуля');

		return true;
	}

	public function activateAction ($moduleId, $status = VALUE_TRUE)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$module = Module::findFirst($moduleId);

		if (!$module)
		{
			$this->flashSession->error('Модуль не найден');

			return $this->response->redirect(self::CODE.'/');
		}

		if ($status == VALUE_TRUE)
			$module->active = VALUE_TRUE;
		if ($status == VALUE_FALSE)
			$module->active = VALUE_FALSE;

		if ($module->update())
			CacheHelper::clearApplicationCache();
		else
		{
			foreach ($module->getMessages() as $message)
			{
				$this->flashSession->error($message);
			}
		}

		return $this->response->redirect(self::CODE.'/');
	}
}