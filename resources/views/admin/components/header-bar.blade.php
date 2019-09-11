<div class="kt-subheader__main">
	@if (!isset($title_hide))
		<h3 class="kt-subheader__title">{{ __('admin.page_title.'.$route_controller.'_'.$route_action) }}</h3>
	@endif
	<span class="kt-subheader__separator kt-hidden"></span>
	<div class="kt-subheader__breadcrumbs">
		<a href="{{ route('admin.index', [], false) }}" class="kt-subheader__breadcrumbs-home">
			<i class="flaticon2-shelter"></i>
		</a>
		@foreach ($breadcrumbs as $item)
			<span class="kt-subheader__breadcrumbs-separator"></span>
			@if (!empty($item['url']))
				<a href="{{ url($item['url']) }}" class="kt-subheader__breadcrumbs-link">{{ $item['title'] }}</a>
			@else
				<span class="kt-subheader__breadcrumbs-link">{{ $item['title'] }}</span>
			@endif
		@endforeach
	</div>
</div>

<div class="kt-subheader__toolbar">
	<div class="kt-subheader__wrapper"></div>
</div>