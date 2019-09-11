<ul class="kt-menu__nav">
	@foreach ($main_menu as $item)
		@if ($item['code'] == null)
			<li class="kt-menu__section">
				<h4 class="kt-menu__section-text">{{ $item['title'] }}</h4>
			</li>
		@else
			<li class="kt-menu__item @if ($route_controller == $item['code'])kt-menu__item--active{{ count($item['childrens']) ? ' kt-menu__item--open' : '' }}@endif{{ count($item['childrens']) ? 'kt-menu__item--submenu' : '' }}">
				<a href="{{ !$item['url'] ? 'javascript:;' : $item['url'] }}" class="kt-menu__link {{ count($item['childrens']) ? 'kt-menu__toggle' : '' }}">
					<span class="kt-menu__link-icon icon flaticon2-{{ $item['icon'] }}"></span>
					<span class="kt-menu__link-text">{{ $item['title'] }}</span>
					@if (count($item['childrens']))
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					@endif
				</a>
				@if (count($item['childrens']))
					<div class="kt-menu__submenu">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item  kt-menu__item--parent">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Applications</span>
								</span>
							</li>
							@foreach ($item['childrens'] as $child)
								<li class="kt-menu__item {{ $route_action == $child['code'] ? 'kt-menu__item--active' : '' }}">
									<a href="{{ $child['url'] }}" class="kt-menu__link">
										@if (isset($child['icon']))
											<span class="icon fa fa-{{ $child['icon'] ?? '' }}"></span>
										@else
											<i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
										@endif
										<span class="kt-menu__link-text">{{ $child['title'] }}</span>
									</a>
								</li>
							@endforeach
						</ul>
					</div>
				@endif
			</li>
		@endif
	@endforeach
</ul>