{{ getDoctype() }}
<html lang="ru">
<head>
	{{ getTitle() }}
	{{ tag.tagHtml('meta', ['name': 'description', 'content': '']) }}
	{{ tag.tagHtml('meta', ['name': 'keywords', 'content': '']) }}
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<link rel="image_src" href="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/logo.jpg') }}" />
	<link rel="apple-touch-icon" href="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/apple-touch-icon.png') }}"/>

	{{ assets.outputCss() }}
	{{ assets.outputJs() }}

	<!--[if lte IE 9]>
		<link rel="stylesheet" href="https://rawgit.com/codefucker/finalReject/master/reject/reject.css" media="all" />
		<script type="text/javascript" src="https://rawgit.com/codefucker/finalReject/master/reject/reject.min.js"></script>
	<![endif]-->

	{% if allowMobile() is not true %}
		<meta name="viewport" content="width=810">
	{% else %}
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script type="text/javascript">
			$(document).ready(function()
			{
				if (!navigator.userAgent.match(/(\(iPod|\(iPhone|\(iPad)/))
				{
					$("body").swipe(
					{
						swipeLeft: function()
						{
							if ($('.menu-sidebar').hasClass('opened'))
								$('.menu-toggle').click();
							else
								$('.planet-toggle').click();
						},
						swipeRight: function()
						{
							if ($('.planet-sidebar').hasClass('opened'))
								$('.planet-toggle').click();
							else
								$('.menu-toggle').click();
						},
						threshold: 100,
						excludedElements: ".table-responsive",
						fallbackToMouseEvents: false,
						allowPageScroll: "auto"
					});
				}
			});
		</script>
	{% endif %}
</head>
<body class="{{ config.view.get('socialIframeView', 0) == 1 ? 'iframe' : 'window' }}">
	<script type="text/javascript">
		XNova.path = '{{ url.getBaseUri() }}';
		timestamp = {{ time() }};
		timezone = {{ timezone }};
		ajax_nav = {{ ajaxNavigation }};
		addToUrl = '';

		{% if auth.isAuthorized() %}
			XNova.fleetSpeed 	= {{ game.getSpeed('fleet') }};
			XNova.gameSpeed 	= {{ (config.game.get('game_speed', 1) / 2500)|round(1) }};
			XNova.resSpeed 		= {{ config.game.get('resource_multiplier', 1) }};
		{% endif %}
	</script>

	<div id="box" class="set_{{ controller }}">
		{% if leftMenu is defined and leftMenu == true %}
			{{ partial('shared/header') }}
		{% endif %}

		<div class="game_content">
			{% if leftMenu is defined and leftMenu == true %}
				{{ partial('shared/menu') }}
			{% endif %}

			{% if leftMenu is defined and leftMenu == true %}
				{{ partial('shared/planets') }}
			{% endif %}

			{% if config.view.get('socialIframeView', 0) == 1 %}<div class="iframe_wrapper">{% endif %}
			<div id="gamediv" class="content">

				{% if topPanel is defined and topPanel == true %}
					{{ partial('shared/panel') }}
				{% endif %}

				{% if deleteUserTimer is defined and deleteUserTimer > 0 %}
					<table class="table"><tr><td class="c" align="center">Включен режим удаления профиля!<br>Ваш аккаунт будет удалён после {{ game.datezone("d.m.Y", deleteUserTimer) }} в {{ game.datezone("H:i:s", deleteUserTimer) }}. Выключить режим удаления можно в настройках игры.</td></tr></table><div class="separator"></div>
				{% endif %}

				{% if vocationTimer is defined and vocationTimer > 0 %}
				   <table class="table"><tr><td class="c negative" align="center">Включен режим отпуска! Функциональность игры ограничена.</td></tr></table><div class="separator"></div>
				{% endif %}

				{% if globalMessage is defined and globalMessage != '' %}
				   <table class="table"><tr><td class="c" align="center">{{ globalMessage }}</td></tr></table><div class="separator"></div>
				{% endif %}

				<div class="content-row">
					{% set messages = flashSession.getMessages() %}

					{% if messages|length > 0 %}
						{% for type, items in messages %}
							{% for message in items %}
								{% if type == 'alert' %}
									<script type="text/javascript">$(document).ready(function(){alert("{{ message }}");});</script>
								{% else %}
									{{ message }}
								{% endif %}
							{% endfor %}
						{% endfor %}
					{% endif %}
					{{ content() }}
				</div>

			</div>
			{% if config.view.get('socialIframeView', 0) == 1 %}</div>{% endif %}
		</div>

		{% if leftMenu is defined and leftMenu == true %}
			<footer class="hidden-xs-down">
				<div class="container-fluid">
					<div class="pull-xs-left text-xs-left">
						<a href="{{ url('news/') }}" title="Последние изменения">{{ constant('VERSION') }}</a>
						{% if config.view.get('socialIframeView', 0) == 0 %}
							<a class="hidden-sm-down" target="_blank" href="http://xnova.su/">© 2008 - {{ date("Y") }} Xcms</a>
						{% endif %}
					</div>
					<div class="pull-xs-right text-xs-right">
						<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
						<a href="{{ url('banned/') }}">Тёмные</a>|
						{% if config.view.get('socialIframeView', 0) == 0 %}
							<a href="//vk.com/xnova_game" target="_blank">ВК</a>|
							<a href="{{ url('contact/') }}">Контакты</a>|
						{% endif %}
						<a href="{{ url('content/help/') }}">Новичкам</a>|
						<a href="{{ url('content/agb/') }}">Правила</a>|
						<a onclick="" title="Игроков в сети" style="color:green">{{ option('users_online', 0) }}</a>/<a onclick="" title="Всего игроков" style="color:yellow">{{ option('users_total', 0) }}</a>
					</div>
					<div class="clearfix"></div>
				</div>
			</footer>
			<div class="row hidden-sm-up footer-mobile">
				<div class="col-xs-12 text-xs-center">
					<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
					<a href="{{ url('banned/') }}">Тёмные</a>|
					<a href="{{ url('contact/') }}">Контакты</a>|
					<a href="{{ url('content/help/') }}">Новичкам</a>|
					<a href="{{ url('content/agb/') }}">Правила</a>
				</div>
				<div class="col-xs-8 text-xs-center">
					<a href="{{ url('news/') }}" title="Последние изменения">{{ constant('VERSION') }}</a>
					{% if config.view.get('socialIframeView', 0) == 0 %}
						<a class="media_1" target="_blank" href="http://xnova.su/">© 2008 - {{ date("Y") }} Xcms</a>
					{% endif %}
				</div>
				<div class="col-xs-4 text-xs-center">
					<a onclick="" title="Игроков в сети" style="color:green">{{ option('users_online', 0) }}</a>/<a onclick="" title="Всего игроков" style="color:yellow">{{ option('users_total', 0) }}</a>
				</div>
			</div>
		{% endif %}
	</div>

	{{ partial('shared/socials') }}

	<div id="windowDialog"></div>
	<div id="tooltip" class="tip"></div>
</body>
</html>