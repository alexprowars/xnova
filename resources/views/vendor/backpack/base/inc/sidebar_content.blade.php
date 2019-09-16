@foreach ($main_menu as $item)
	@if ($item['code'] == null)
		<li class="header">{{ $item['title'] }}</li>
	@else
		<li class="@if ($route_controller == $item['code'])active{{ count($item['childrens']) ? ' menu-open' : '' }}@endif{{ count($item['childrens']) ? 'treeview' : '' }}">
			<a href="{{ !$item['url'] ? 'javascript:;' : $item['url'] }}">
				<i class="fa fa-{{ $item['icon'] }}"></i>
				<span>{{ $item['title'] }}</span>
				@if (count($item['childrens']))
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				@endif
			</a>
			@if (count($item['childrens']))
				<ul class="treeview-menu">
					@foreach ($item['childrens'] as $child)
						<li class="{{ $route_action == $child['code'] ? 'active' : '' }}">
							<a href="{{ $child['url'] }}">
								@if (isset($child['icon']))
									<i class="fa fa-{{ $child['icon'] ?? '' }}"></i>
								@else
									<i class="fa fa-circle-o"></i>
								@endif
								{{ $child['title'] }}
							</a>
						</li>
					@endforeach
				</ul>
			@endif
		</li>
	@endif
@endforeach