<form action="{{ url("overview/rename/pl/"~parse['planet_id']~"/") }}" method="POST">
	<table class="table">
		<tr>
			<td class="c" colspan="3">Переименовать или покинуть планету</td>
		</tr>
		{% if isPopup is not defined %}
			<tr>
				<th class="hidden-xs-down">{{ parse['galaxy_galaxy'] }}:{{ parse['galaxy_system'] }}:{{ parse['galaxy_planet'] }}</th>
				<th>{{ parse['planet_name'] }}</th>
				<th><a href="{{ url("overview/delete/") }}"><input type="button" value="Покинуть колонию" alt="Покинуть колонию"></a></th>
			</tr>
		{% endif %}
		<tr>
			<th class="hidden-xs-down">Сменить название</th>
			<th><input type="text" placeholder="{{ parse['planet_name'] }}" name="newname" maxlength=20></th>
			<th><input type="submit" name="action" value="Сменить название"></th>
		</tr>
	</table>
</form>
{% if parse['type'] != '' %}
	<div class="separator"></div>
	<form action="{{ url("overview/rename/pl/"~parse['planet_id']~"/") }}" method="POST">
		<table class="table">
			<tr>
				<td class="c">Сменить фон планеты</td>
			</tr>
			<tr>
				<th>
					<div class="row">
						{% for i in 1..parse['images'][parse['type']] %}
							<div class="col-xs-6 col-sm-3 col-md-2">
								<input type="radio" name="image" value="{{ i }}" id="image_{{ i }}">
								<label for="image_{{ i }}"><img src="{{ url.getBaseUri() }}assets/images/planeten/small/s_{{ parse['type'] }}planet{{ (i < 10 ? '0' : '')~i }}.jpg" align="absmiddle" width="80"></label>
							</div>
						{% endfor %}
					</div>
				</th>
			</tr>
			<tr>
				<th>
					<input type="submit" name="action" value="Сменить картинку (1 кредит)">
				</th>
			</tr>
		</table>
	</form>
{% endif %}