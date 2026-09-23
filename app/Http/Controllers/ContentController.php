<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models\Content;
use Illuminate\Http\Request;
use Inertia\Inertia;
use InertiaUI\Modal\Modal;

class ContentController extends Controller
{
	public function index(string $slug, Request $request)
	{
		if (empty($slug)) {
			throw new Exception(__('main.content_page_not_found'));
		}

		$content = Content::query()
			->where('alias', $slug)
			->first();

		if (!$content) {
			throw new Exception(__('main.content_page_not_found'));
		}

		$result = [
			'title' => $content->title,
			'body' => stripslashes($content->html),
		];

		$component = $request->hasHeader(Modal::HEADER_MODAL)
			? 'Content/Modal' : 'Content/Detail';

		return Inertia::modal($component, $result)
			->baseRoute('content', [$content->alias]);
	}
}
