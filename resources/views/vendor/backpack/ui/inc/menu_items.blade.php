@foreach ($main_menu as $item)
	@if ($item['code'] == null)
		<li class="nav-title">{{ $item['title'] }}</li>
	@else
		<li class="nav-item @if ($route_controller == $item['code'])active{{ count($item['childrens']) ? ' open' : '' }}@endif{{ count($item['childrens']) ? 'nav-dropdown' : '' }}">
			<a href="{{ !$item['url'] ? 'javascript:;' : $item['url'] }}" class="nav-link {{ $route_controller == $item['code'] ? ' active' : '' }}">
				<i class="nav-icon fa fa-{{ $item['icon'] }}"></i>
				{{ $item['title'] }}
			</a>
			@if (count($item['childrens']))
				<ul class="nav-dropdown-items">
					@foreach ($item['childrens'] as $child)
						<li class="nav-item {{ $route_action == $child['code'] ? 'active' : '' }}">
							<a href="{{ $child['url'] }}" class="nav-link {{ $route_action == $child['code'] ? ' active' : '' }}">
								@if (isset($child['icon']))
									<i class="nav-icon fa fa-{{ $child['icon'] ?? '' }}"></i>
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