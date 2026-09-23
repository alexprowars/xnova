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

		$locale = app()->getLocale();
		$fallbackLocale = app()->getFallbackLocale();

		$result = [
			'title' => $content->{'title_' . $locale} ?: ($content->{'title_' . $fallbackLocale} ?? ''),
			'body' => stripslashes($content->{'html_' . $locale} ?: ($content->{'html_' . $fallbackLocale} ?? '')),
		];

		if ($request->hasHeader(Modal::HEADER_MODAL)) {
			return Inertia::modal('Content/Modal', $result)
				->baseRoute('content', [$content->alias]);
		}

		return Inertia::render('Content/Detail', $result);
	}
}
