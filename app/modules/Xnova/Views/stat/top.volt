<form method="post" action="{{ url('stat/'~(who != '' ? who~'/' : '')) }}" id="stats">
	<input type="hidden" name="old_who" value="{{ who }}">
	<input type="hidden" name="old_type" value="{{ type }}">
	<div class="table">
		<div class="row">
			<div class="c col-12">Статистика: {{ update }}</div>
		</div>
		<div class="row">
			<div class="th col-2 middle">Какой</div>
			<div class="th col-4 col-sm-2">
				<select name="who" onChange="$(this).parents('form').attr('action', {{ url('stat/') }}+$(this).val()+'/');$(this).parents('form').submit()" title="">
					{% for key, value in _text('xnova', 'who') %}
						<option value="{{ key }}" {{ key == who ? 'selected' : '' }}>{{ value }}</option>
					{% endfor %}
				</select>
			</div>
			<div class="th col-2 col-sm-1 middle">по</div>
			<div class="th col-4 col-sm-3">
				<select name="type" onChange="$(this).parents('form').submit()" title="">
					{% for key, value in _text('xnova', 'type_'~who) %}
						<option value="{{ key }}" {{ key == type ? 'selected' : '' }}>{{ value }}</option>
					{% endfor %}
				</select>
			</div>
			<div class="th col-2 middle">на месте</div>
			<div class="th col-10 col-sm-2"><select name="range" onChange="$(this).parents('form').submit()" title="">{{ range }}</select></div>
		</div>
	</div>
</form>
<div class="separator"></div>