<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;

class ContentController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'content',
			'title' => 'Контент',
			'icon'	=> 'writing',
			'sort'	=> 190
		], [
			'code'	=> null,
			'title' => 'Администрирование',
			'icon'	=> '',
			'sort'	=> 200
		]];
	}

	public function index ()
	{
		$result = [];

		$result['rows'] = [];

		$query = $this->db->query("SELECT * FROM content");

		$result['total'] = $query->numRows();

		while ($e = $query->fetch())
		{
			$result['rows'][] = $e;
		}

		View::share('title', "Контент");

		return view('admin.content.index', ['parse' => $result]);
	}

	public function edit ($id)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$info = $this->db->query("SELECT * FROM content WHERE id = '".intval($id)."'")->fetch();

		return view('admin.content.edit', ['info' => $info]);
	}
}