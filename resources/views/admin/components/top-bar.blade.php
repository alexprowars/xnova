<div class="kt-header__topbar-item kt-header__topbar-item--user">
	<div class="kt-header__topbar-wrapper" data-toggle="dropdown">
		<div class="kt-header__topbar-user">
			<span class="kt-header__topbar-username">{{ $user->username }}</span>
			@if (false)
				<img alt="" src="{{ $user->photo }}" />
			@else
				<img alt="" src="/assets/admin/images/default-avatar.jpg" />
			@endif
		</div>
	</div>
	<div class="dropdown-menu dropdown-menu-right">
		@if ($user->can('show users'))
			<a class="dropdown-item" href="{{ url('users/edit/'.$user->id.'/') }}"><i class="ti-user"></i>Профиль</a>
		@endif
		<div class="dropdown-divider"></div>
		<a class="dropdown-item" href="{{ url('logout/') }}"><i class="ti-power-off"></i> Выход</a>
	</div>
</div>