{{ getDoctype() }}
<html lang="ru">
<head>
   	<meta charset="utf-8" />
	{{ getTitle() }}
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	{{ assets.outputCss() }}
	{{ assets.outputJs() }}
   <link rel="shortcut icon" href="/favicon.ico" />
</head>
<body>
	<div class="preloader">
		<div class="spinner-dots">
			<span class="dot1"></span>
			<span class="dot2"></span>
			<span class="dot3"></span>
		</div>
	</div>

	<aside class="sidebar sidebar-icons-right sidebar-icons-boxed sidebar-expand-lg">
		<header class="sidebar-header bg-dark">
			<span class="logo">
				<a href="/admin/">
					GAME FRAMEWORK
				</a>
    		</span>
			<span class="sidebar-toggle-fold"></span>
		</header>

		<nav class="sidebar-navigation">
			<ul class="menu menu-sm">
				{% for item in main_menu %}
					{% if item['code'] == 'separator' %}
						<li class="menu-category">{{ item['title'] }}</li>
					{% else %}
						<li class="menu-item {% if route_controller == item['code'] %}active{{ item['childrens']|length ? ' open' : '' }}{% endif %}">
							<a href="{{ item['url'] is defined and item['url'] !== false ? (item['url'] == '' ? 'javascript:;' : url(item['url']~'/')) : url(item['code']~'/') }}" class="menu-link">
								<span class="icon fa fa-{{ item['icon'] }}"></span>
								<span class="title">{{ item['title'] }}</span>
								{% if item['childrens']|length %}
									<span class="arrow"></span>
								{% endif %}
							</a>
							{% if item['childrens']|length %}
								<ul class="menu-submenu">
									{% for child in item['childrens'] %}
										<li class="menu-item {% if route_action == child['code'] %}active{% endif %}">
											<a href="{{ child['url'] is defined and child['url'] != '' ? url(child['url']~'/') : url(item['code']~'/'~child['code']~'/') }}" class="menu-link">
												{% if child['icon'] is defined %}
													<span class="icon fa fa-{{ child['icon'] }}"></span>
												{%  else %}
													<span class="dot"></span>
												{% endif %}
												<span class="title">{{ child['title'] }}</span>
											</a>
										</li>
									{% endfor %}
								</ul>
							{% endif %}
						</li>
					{% endif %}
				{% endfor %}
			</ul>
		</nav>
	</aside>

	<header class="topbar topbar-inverse">
		<div class="topbar-left">
			<span class="topbar-btn sidebar-toggler"><i>&#9776;</i></span>

			<a class="topbar-btn d-none d-md-block" href="#" data-provide="fullscreen tooltip" title="Fullscreen">
				<i class="material-icons fullscreen-default">fullscreen</i>
				<i class="material-icons fullscreen-active">fullscreen_exit</i>
			</a>

			<div class="lookup d-none d-md-block topbar-search" id="theadmin-search">
				<input class="form-control w-300px" type="text">
				<div class="lookup-placeholder">
					<i class="ti-search"></i>
				</div>
			</div>
		</div>

		<div class="topbar-right">
			<ul class="topbar-btns">
				<li class="dropdown">
					<span class="topbar-btn" data-toggle="dropdown">
						{% if user_photo != '' %}
							<img alt="" class="avatar" src="{{ user_photo }}" />
						{% else %}
							<img alt="" class="avatar" src="/assets/admin/images/default-avatar.jpg" />
						{% endif %}
					</span>
					<div class="dropdown-menu dropdown-menu-right">
						{% if access.canReadController('users', 'admin') %}
							<a class="dropdown-item" href="{{ url('users/edit/'~user_id~'/') }}"><i class="ti-user"></i>Профиль</a>
						{% endif %}
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="{{ url('logout/') }}"><i class="ti-power-off"></i> Выход</a>
					</div>
				</li>
				{% if access.canReadController('notifications', 'admin') %}
					{% set ncount = notifications|length %}
					<li class="dropdown d-none d-md-block">
						<span class="topbar-btn {% if notifications|length %}has-new{% endif %}" data-toggle="dropdown"><i class="ti-bell"></i></span>
						<div class="dropdown-menu dropdown-menu-right">
							<div class="media-list media-list-hover media-list-divided media-list-xs">
								{% for notification in notifications %}
									<a class="media" href="javascript:;">
										<span class="avatar bg-{{ notification.priority }}">
											{% if notification.type == "default" %}
												<i class="ti-user"></i>
											{% else %}
												<i class="ti-{{ notification.type }}"></i>
											{% endif %}
											<i class="ti-shopping-cart"></i>
										</span>
										<div class="media-body">
											<p>{{ notification.message }}</p>
											<time datetime="2017-07-14 20:00">{{ notification.pastTimeFormat() }}</time>
										</div>
									</a>
								{% endfor %}
							</div>
						</div>
					</li>
				{% endif %}
			</ul>
		</div>
	</header>

	<main>
		<header class="header no-border">
			<div class="header-bar header-transparent">
				{% if title_hide is not defined %}
					<h4>{{ _text('admin', 'page_title', route_controller~'_'~route_action) }}</h4>
				{% endif %}
				<ol class="breadcrumb">
					<li class="breadcrumb-item">
						<a href="{{ url('') }}">На главную</a>
					</li>
					{% for item in breadcrumbs %}
						<li class="breadcrumb-item">
							{% if item['url'] is not empty %}
								<a href="{{ url(item['url']) }}">{{ item['title'] }}</a>
							{% else %}
								<span>{{ item['title'] }}</span>
							{% endif %}
						</li>
					{% endfor %}
				</ol>
			</div>
		</header>
		<div class="main-content">
			{{ flashSession.output() }}
			{{ content() }}
		</div>
		<footer class="site-footer">
			<div class="row">
				<div class="col-md-6">
					<p class="text-center text-md-left">Copyright © {{ date('Y') }} <a href="http://xnova.su">Xnova Online</a>. All rights reserved.</p>
				</div>

				<div class="col-md-6">
					<ul class="nav nav-primary nav-dotted nav-dot-separated justify-content-center justify-content-md-end">
						<li class="nav-item"></li>
					</ul>
				</div>
			</div>
		</footer>
	</main>
</body>
</html>