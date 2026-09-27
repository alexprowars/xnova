<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContentController extends AdminController
{
	public function index(Request $request)
	{
		Access::authorize('content');

		$columns = ['id', 'alias', 'title_ru', 'title_en'];

		return $this->listing(
			$request,
			'content',
			Content::query(),
			$columns,
			[Presentation::field('alias'), Presentation::field('title_ru'), Presentation::field('title_en')],
			fn(Content $content) => Presentation::record($content, $columns),
		);
	}

	private function fields(?Content $content = null): array
	{
		return Presentation::fill(
			array_map(
				fn(string $key) => Presentation::field(
					$key,
					str_starts_with($key, 'html_') ? 'textarea' : 'text',
				),
				['alias', 'title_ru', 'html_ru', 'title_en', 'html_en'],
			),
			$content,
		);
	}

	public function create()
	{
		Access::authorize('content');

		return $this->form('content', $this->fields());
	}

	public function edit(Content $content)
	{
		Access::authorize('content');

		return $this->form('content', $this->fields($content), $content->id);
	}

	public function store(Request $request)
	{
		return $this->update($request, new Content());
	}

	public function update(Request $request, Content $content)
	{
		Access::authorize('content');

		$data = $request->validate([
			'alias' => ['required', 'alpha_dash', 'max:100', Rule::unique('contents')->ignore($content->id)],
			'title_ru' => 'required|string|max:150',
			'title_en' => 'nullable|string|max:150',
			'html_ru' => 'required|string|max:16000',
			'html_en' => 'nullable|string|max:16000',
		]);

		$content->fill($data)
			->save();

		return redirect('/admin/content/' . $content->id . '/edit')->with(
			'admin_notice',
			__('admin.saved'),
		);
	}
}
