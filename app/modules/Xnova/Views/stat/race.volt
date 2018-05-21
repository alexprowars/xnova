<div class="table middle">
	<div class="row">
		<div class="c col-2">Место</div>
		<div class="c col-1">&nbsp;</div>
		<div class="c col-3">Игроков</div>
		<div class="c col-3">Очков</div>
		<div class="c col-3">Очки на игрока</div>
	</div>
	{% if stat|length > 0 %}
		{% for s in stat %}
			<div class="row">
				<div class="th col-2">{{ s['rank'] }}</div>
				<div class="th col-1 text-center"><img src="{{ url.getBaseUri() }}assets/images/skin/race{{ s['race'] }}.gif" width="30" height="30"></div>
				<div class="th col-3">{{ s['count'] }}</div>
				<div class="th col-3">{{ s['points'] }}</div>
				<div class="th col-3">{{ s['pointatuser'] }}</div>
			</div>
		{% endfor %}
	{% endif %}
</div>