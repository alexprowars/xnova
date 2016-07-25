<form action="{{ url('alliance/search/') }}" method="POST">
	<table class="table">
		<tr>
			<td class="c" colspan="2">Поиск альянса</td>
		</tr>
		<tr>
			<th>Строка поиска</th>
			<th>
				<input type="text" name="searchtext" value="{{ parse['searchtext'] }}" title=""><input type="submit" value="Поиск">
			</th>
		</tr>
	</table>
</form>

{% if (isset($parse['result']) and count($parse['result']) > 0 %}
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="3">Найденые альянсы:</td>
		</tr>
		{% for parse['result'] AS $r %}
			<tr>
				<th class="text-xs-center">
					{{ r['tag'] }}
				</th>
				<th class="text-xs-center">
					{{ r['name'] }}
				</th>
				<th class="text-xs-center">
					{{ r['members'] }}
				</th>
			</tr>
		{% endfor %}
		<tr>
	</table>
{% endif %}
