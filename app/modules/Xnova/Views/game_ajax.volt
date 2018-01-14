{% if topPanel is defined and topPanel == true %}
	<script type="text/javascript">
		options.planet = {{ toJson(planet) }};
		timeouts['res_count'] = window.setInterval(XNova.updateResources, 1000);
	</script>
{% elseif isPopup is not true %}
	<script type="text/javascript">
		options.planet = false;
	</script>
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

{% if userId > 0 and isPopup is not true %}
	{{ partial('shared/planets_ajax') }}
{% endif %}

{% if isPopup is false %}
	<script type="text/javascript">
		options.route.controller = '{{ dispatcher.getControllerName() }}';
		options.route.action = '{{ dispatcher.getActionName() }}';
	</script>
{% endif %}

{% if userId > 0 %}
	<script type="text/javascript">
		UpdateGameInfo('{{ messages }}', '{{ messages_ally }}');
		{% if isPopup is false %}
			timestamp = {{ time() }};
		{% endif %}
	</script>
{% endif %}