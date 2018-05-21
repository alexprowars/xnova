<div class="table">
	<div class="row">
		<div class="c col-sm-1 col-2 middle">Место</div>
		<div class="c col-sm-1 d-none d-sm-block middle">+/-</div>
		<div class="c col-sm-5 col-4 middle">Альянс</div>
		<div class="c col-sm-1 col-2 middle">Игроки</div>
		<div class="c col-sm-2 d-none d-sm-block middle">Очки</div>
		<div class="c col-sm-2 d-none d-sm-block middle">Очки на игрока</div>
		<div class="c d-sm-none col-4 middle">Очки / Очки на игрока</div>
	</div>
	{% for s in stat %}
		<div class="row">
			<div class="th col-sm-1 col-2">
				{{ s['rank'] }}
				<div class="d-sm-none">{{ s['rankplus'] }}</div>
			</div>
			<div class="th col-sm-1 d-none d-sm-block">{{ s['rankplus'] }}</div>
			<div class="th col-sm-5 col-4 middle">{{ s['name'] }}</div>
			<div class="th col-sm-1 col-2 middle">{{ s['members'] }}</div>
			<div class="th col-sm-2 d-none d-sm-block"><a href="{{ url('alliance/stat/id/'~s['id']~'/') }}">{{ s['points'] }}</a></div>
			<div class="th col-sm-2 d-none d-sm-block">{{ s['members_points'] }}</div>
			<div class="th d-sm-none col-4">
				<a href="{{ url('alliance/stat/id/'~s['id']~'/') }}">{{ s['points'] }}</a>
				<br>
				{{ s['members_points'] }}
			</div>
		</div>
	{% endfor %}
</div>