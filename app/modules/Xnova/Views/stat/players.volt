<div class="table">
	<div class="row">
		<div class="c col-sm-1 col-2 middle">Место</div>
		<div class="c col-sm-1 d-none d-sm-block middle">+/-</div>
		<div class="c col-sm-4 col-5 middle">Игрок</div>
		<div class="c col-sm-1 col-2 middle">&nbsp;</div>
		<div class="c col-sm-3 d-none d-sm-block middle">Альянс</div>
		<div class="c col-sm-2 col-3 middle">Очки</div>
	</div>
	{% for s in stat %}
		<div class="row">
			<div class="th col-sm-1 col-2">
				{{ s['rank'] }}
				<div class="d-sm-none">{{ s['rankplus'] }}</div>
			</div>
			<div class="th col-sm-1 d-none d-sm-block">{{ s['rankplus'] }}</div>
			<div class="th col-sm-4 col-5">
				<a href="{{ url('players/'~s['id']~'/') }}" class="window popup-user">{{ s['name'] }}</a>
				<div class="d-sm-none">
					{{ s['alliance'] }}
				</div>
			</div>
			<div class="th col-sm-1 col-2">
				{% if s['race'] != 0 %}<img src="{{ url.getBaseUri() }}assets/images/skin/race{{ s['race'] }}.gif" width="16" height="16" class="float-left" style="margin-left:7px;">{% endif %}
				{% if userId is defined and userId != 0 %}{{ s['mes'] }}{% endif %}
			</div>
			<div class="th col-sm-3 d-none d-sm-block">{{ s['alliance'] }}</div>
			<div class="th col-sm-2 col-3 middle">
				<a href="{{ url('players/stat/'~s['id']~'/') }}">{{ s['points'] }}</a>
			</div>
		</div>
	{% endfor %}
</div>