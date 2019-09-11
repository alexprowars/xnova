<?php

namespace Xnova;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class AdminController extends BaseController
{
	private $breadcrumbs = [];
	/** @var User */
	protected $user;

	public function __construct ()
	{
		$this->middleware(function ($request, $next)
		{
			$this->user = Auth::user();

			return $next($request);
		});
	}

	public function addToBreadcrumbs ($title, $url = '')
	{
		$this->breadcrumbs[] = [
			'url' 	=> trim($url, '/ ').'/',
			'title' => trim($title)
		];
	}

	public function getBreadcrumbs ()
	{
		return $this->breadcrumbs;
	}
}