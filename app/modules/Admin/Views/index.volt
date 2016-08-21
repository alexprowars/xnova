{{ getDoctype() }}
<html>
<head>
   	<meta charset="utf-8" />
	{{ getTitle() }}
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	<meta name="MobileOptimized" content="320">
	{{ assets.outputCss() }}
	{{ assets.outputJs() }}
   <link rel="shortcut icon" href="/favicon.ico" />
</head>
<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white">
	<div class="page-header navbar navbar-fixed-top">
		<div class="page-header-inner ">
			<div class="page-logo">
				<a href="/"><img src="{{ static_url('assets/admin/images/logo.png') }}" alt="logo" class="logo-default" /> </a>
				<div class="menu-toggler sidebar-toggler"><span></span></div>
			</div>
			<form class="search-form search-form-expanded" action="{{ url('search/') }}" method="GET">
				<div class="input-group">
					<input type="text" class="form-control" placeholder="Поиск..." name="q">
					<span class="input-group-btn">
						<a href="javascript:;" class="btn submit">
							<i class="icon-magnifier"></i>
						</a>
					</span>
				</div>
			</form>
			<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
				<span></span>
			</a>
			<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> </a>
			<div class="top-menu">
				<ul class="nav navbar-nav pull-right">
					{% if access.canReadController('notifications', 'admin') %}
						{% set ncount = notifications|length %}
						<li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
							<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
								<i class="icon-bell"></i>
								{% if ncount %}
									<span class="badge badge-default"> {{ ncount }} </span>
								{% endif %}
							</a>
							<ul class="dropdown-menu">
								<li class="external">
									{% if ncount %}
										<h3><span class="bold">{{ ncount }}</span> {{ plural(ncount, ['новое', 'новых', 'новых']) }} {{ plural(ncount, ['уведомление', 'уведомлений', 'уведомлений']) }}</h3>
										<a href="{{ url('notifications/') }}">просмотреть</a>
									{% else %}
										<h3>Нет новых уведомлений</h3>
										<a href="{{ url('notifications/') }}">все</a>
									{% endif %}
								</li>
								<li>
									<ul class="dropdown-menu-list scroller" style="height: 250px;" data-handle-color="#637283">
										{% for notification in notifications %}
											<li>
												<a href="javascript:;">
													<span class="time">{{ notification.pastTimeFormat() }}</span>
													<span class="details">
														<span class="label label-sm label-icon label-{{ notification.priority }}">
															{% if notification.type == "default" %}
																<i class="fa fa-bell"></i>
															{% else %}
																<i class="fa fa-{{ notification.type }}"></i>
															{% endif %}
														</span>
														{{ notification.message }}
													</span>
												</a>
											</li>
										{% endfor %}
									</ul>
								</li>
							</ul>
						</li>
					{% endif %}
					<li class="dropdown dropdown-user">
						<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
							{% if user_photo != '' %}
								<img alt="" class="img-circle" src="{{ user_photo }}" />
							{% endif %}
							<span class="username username-hide-on-mobile">{{ user_full_name }}</span>
							<i class="fa fa-angle-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-default">
							{% if access.canReadController('users', 'admin') %}
								<li>
									<a href="{{ url('users/edit/'~user_id~'/') }}"><i class="icon-user"></i>Профиль</a>
								</li>
							{% endif %}
							<li>
								<a href="{{ url('logout/') }}"><i class="icon-key"></i>Выход</a>
							</li>
						</ul>
					</li>

					<li class="dropdown dropdown-language">
						<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"
						   data-close-others="true">
							<img alt="" src="{{ static_url('/assets/admin/global/img/flags/ru.png') }}">
							<span class="langname">RU</span>
							<i class="fa fa-angle-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-default">
							<li>
								<a href="javascript:;">
									<img alt="" src="{{ static_url('/assets/admin/global/img/flags/ru.png') }}"> Русский
								</a>
							</li>
						</ul>
					</li>

					<li class="dropdown dropdown-quick-sidebar-toggler">
						<a href="{{ url('logout/') }}" class="dropdown-toggle" title="Выйти">
							<i class="icon-logout"></i>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="clearfix"> </div>
	<div class="page-container">
		<div class="page-sidebar-wrapper">
			<div class="page-sidebar navbar-collapse collapse">
				<ul class="page-sidebar-menu  page-header-fixed page-sidebar-menu-light" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
					<li class="sidebar-toggler-wrapper hide">
						<div class="sidebar-toggler"> </div>
					</li>
					{% for item in main_menu %}
						<li class="nav-item start {% if route_controller == item['code'] %}active{{ item['childrens']|length == item['code'] ? ' open' : '' }}{% endif %}">
							<a href="{{ item['url'] is defined and item['url'] !== false ? (item['url'] == '' ? 'javascript:;' : url(item['url']~'/')) : url(item['code']~'/') }}" class="nav-link nav-toggle">
								<i class="icon-{{ item['icon'] }}"></i>
								<span class="title">{{ item['title'] }}</span>
								{% if route_controller == item['code'] %}
									<span class="selected"></span>
								{% endif %}
								{% if item['childrens']|length %}
									<span class="arrow {{ route_controller == item['code'] ? 'open' : '' }}"></span>
								{% endif %}
							</a>
							{% if item['childrens']|length %}
								<ul class="sub-menu">
									{% for child in item['childrens'] %}
										<li class="nav-item {% if route_action == child['code'] %}active{% endif %}">
											<a href="{{ child['url'] is defined and child['url'] != '' ? url(child['url']~'/') : url(item['code']~'/'~child['code']~'/') }}" class="nav-link ">
												{% if child['icon'] is defined %}
													<i class="icon-{{ item['icon'] }}"></i>
												{% endif %}
												<span class="title">{{ child['title'] }}</span>
											</a>
										</li>
									{% endfor %}
								</ul>
							{% endif %}
						</li>
					{% endfor %}
				</ul>
			</div>
		</div>
		<div class="page-content-wrapper">
			<div class="page-content">
				<div class="page-bar">
					<ul class="page-breadcrumb">
						<li>
							<a href="{{ url('') }}">На главную</a>
							<i class="fa fa-circle"></i>
						</li>
						{% for item in breadcrumbs %}
							<li>
								{% if item['url'] is not empty %}
									<a href="{{ url(item['url']) }}">{{ item['title'] }}</a>
								{% else %}
									<span>{{ item['title'] }}</span>
								{% endif %}
								{% if not loop.last %}
									<i class="fa fa-circle"></i>
								{% endif %}
							</li>
						{% endfor %}
					</ul>
				</div>
				{% if title_hide is not defined %}
					<h1 class="page-title">{{ _text('admin', 'page_title', route_controller~'_'~route_action) }}</h1>
				{% else %}
					<br>
				{% endif %}
				{{ flashSession.output() }}
				{{ content() }}
			</div>
		</div>
	</div>
	<div class="page-footer">
		<div class="page-footer-inner"> {{ date('Y') }} &copy; <a href="http://xnova.online" class="font-default" target="_blank">Xnova&nbsp;Online</a></div>
		<div class="scroll-to-top">
			<i class="icon-arrow-up"></i>
		</div>
	</div>
</body>
</html>