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

	<meta property="og:image" content="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/logo.jpg') }}"/>
	<meta property="og:image:width" content="300"/>
	<meta property="og:image:height" content="300"/>

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

		var options = {{ toJson(options) }};
	</script>

	<div id="application" v-bind:class="['set_'+route.controller]">
		{% if leftMenu is defined and leftMenu == true %}
			{{ partial('shared/header') }}
		{% endif %}

		<div class="game_content">
			<main-menu v-if="options.view.menu" v-bind:items="menu" v-bind:active="getMenuActiveLink"></main-menu>

			{% if leftMenu is defined and leftMenu == true %}
				{{ partial('shared/planets') }}
			{% endif %}

			<div class="content">
				<planet-panel v-if="options.planet !== false" v-bind:planet="planet"></planet-panel>

				<div id="gamediv">

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
			</div>
		</div>

		<application-footer v-if="options.view.header"></application-footer>
	</div>

	{{ partial('shared/socials') }}

	<div id="windowDialog"></div>
	<div id="tooltip" class="tip"></div>
</body>
</html>