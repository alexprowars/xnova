<?php

namespace Admin\Controllers;

use Admin\Controller;

class TreeController extends Controller
{
	public function indexAction ()
	{
		$this->tag->setTitle('Структура');
	}

	public function nodeAction ()
	{
		$result = [];

		$parent = $this->request->get('parent', 'int', 0);

		$nodes = $this->getMenu($parent, 2);

		foreach ($nodes AS $node)
		{
			$result[] = [
				'id' 		=> $node['id'],
				'text' 		=> $node['name'],
				'type'		=> (count($node['children']) > 0) ? 'folder' : 'file',
				'children' 	=> (count($node['children']) > 0),
				'state'		=> ['opened' => false]
			];
		}

		return $this->response->setJsonContent($result);
	}
}