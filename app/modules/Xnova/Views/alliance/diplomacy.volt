<table class="table">
	<tr>
		<td class="c" colspan="3">Ваши запросы</td>
	</tr>
	{% if parse['DMyQuery'] is defined and parse['DMyQuery']|length %}
		{% for diplo in parse['DMyQuery'] %}
			<tr>
				<th>{{ diplo['name'] }}</th>
				<th>{{ _text('xnova', 'diplomacyStatus', diplo['type']) }}</th>
				<th>
					<router-link to="{{ url('alliance/diplomacy/edit/del/id/'~diplo['id']~'/') }}"><img src="{{ url.getBaseUri() }}assets/images/abort.gif" alt="Удалить заявку"></router-link>
				</th>
			</tr>
		{% endfor %}
	{% else %}
		<tr><th colspan="3">нет</th></tr>
	{% endif %}
</table>
<div class="separator"></div>
<table class="table">
	<tr>
		<td class="c" colspan="3">Запросы вашему альянсу</td>
	</tr>
	{% if parse['DQuery'] is defined and parse['DQuery']|length %}
		{% for diplo in parse['DQuery'] %}
			<tr>
				<th>{{ diplo['name'] }}</th>
				<th>{{ _text('xnova', 'diplomacyStatus', diplo['type']) }}</th>
				<th>
					<router-link to="{{ url('alliance/diplomacy/edit/suc/id/'~diplo['id']~'/') }}"><img src="{{ url.getBaseUri() }}assets/images/appwiz.gif" alt="Подтвердить"></router-link>
					<router-link to="{{ url('alliance/diplomacy/edit/del/id/'~diplo['id']~'/') }}"><img src="{{ url.getBaseUri() }}assets/images/abort.gif" alt="Удалить заявку"></router-link>
				</th>
			</tr>
		{% endfor %}
	{% else %}
		<tr><th colspan="3">нет</th></tr>
	{% endif %}
</table>
<div class="separator"></div>
<table class="table">
	<tr>
		<td class="c" colspan="4">Отношения между альянсами</td>
	</tr>
	{% if parse['DText'] is defined and parse['DText']|length %}
		{% for diplo in parse['DText'] %}
			<tr>
				<th>{{ diplo['name'] }}</th>
				<th>{{ _text('xnova', 'diplomacyStatus', diplo['type']) }}</th>
				<th>
					<router-link to="{{ url('alliance/diplomacy/edit/del/id/'~diplo['id']~'/') }}"><img src="{{ url.getBaseUri() }}assets/images/abort.gif" alt="Удалить заявку"></router-link>
				</th>
			</tr>
		{% endfor %}
	{% else %}
		<tr><th colspan="4">нет</th></tr>
	{% endif %}
</table>
<div class="separator"></div>
<router-form action="{{ url('alliance/diplomacy/edit/add/') }}">
	<table class="table">
		<tr>
			<td class="c" colspan="2">Добавить альянс в список</td>
		</tr>
		<tr>
			<th>
				<select name="ally" title="">
					<option value="0">список альянсов</option>
					{% for item in parse['a_list'] %}
						<option value="{{ item['id'] }}">{{ item['name'] }} [{{ item['tag'] }}]</option>
					{% endfor %}
				</select>
			</th>
			<th>
				<select name="status" title="">
					<option value="1">Перемирие</option>
					<option value="2">Мир</option>
					<option value="3">Война</option>
				</select>
			</th>
		</tr>

		<tr>
			<td class="c"><router-link to="{{ url('alliance/') }}">назад</router-link></td>
			<td class="c">
				<input type="submit" value="Добавить">
			</td>
		</tr>
	</table>
</router-form>