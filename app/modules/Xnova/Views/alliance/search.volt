<router-form action="{{ url('alliance/search/') }}">
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
</router-form>

{% if parse['result'] is defined and parse['result']|length > 0 %}
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="3">Найденые альянсы:</td>
		</tr>
		{% for r in parse['result'] %}
			<tr>
				<th class="text-center">
					{{ r['tag'] }}
				</th>
				<th class="text-center">
					{{ r['name'] }}
				</th>
				<th class="text-center">
					{{ r['members'] }}
				</th>
			</tr>
		{% endfor %}
		<tr>
	</table>
{% endif %}
