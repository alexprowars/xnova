<div class="table">
	<div class="row">
		<div class="c col-sm-1 col-xs-2 middle">Место</div>
		<div class="c col-sm-1 hidden-xs-down middle">+/-</div>
		<div class="c col-sm-5 col-xs-4 middle">Альянс</div>
		<div class="c col-sm-1 col-xs-2 middle">Игроки</div>
		<div class="c col-sm-2 hidden-xs-down middle">Очки</div>
		<div class="c col-sm-2 hidden-xs-down middle">Очки на игрока</div>
		<div class="c hidden-sm-up col-xs-4 middle">Очки / Очки на игрока</div>
	</div>
	{% for stat AS $s %}
		<div class="row">
			<div class="th col-sm-1 col-xs-2">
				{{ s['rank'] }}
				<div class="hidden-sm-up">{{ s['rankplus'] }}</div>
			</div>
			<div class="th col-sm-1 hidden-xs-down">{{ s['rankplus'] }}</div>
			<div class="th col-sm-5 col-xs-4 middle">{{ s['name'] }}</div>
			<div class="th col-sm-1 col-xs-2 middle">{{ s['members'] }}</div>
			<div class="th col-sm-2 hidden-xs-down"><a href="{{ url('alliance/stat/id/'~s['id']~'/') }}">{{ s['points'] }}</a></div>
			<div class="th col-sm-2 hidden-xs-down">{{ s['members_points'] }}</div>
			<div class="th hidden-sm-up col-xs-4">
				<a href="{{ url('alliance/stat/id/'~s['id']~'/') }}">{{ s['points'] }}</a>
				<br>
				{{ s['members_points'] }}
			</div>
		</div>
	{% endfor %}
</div>