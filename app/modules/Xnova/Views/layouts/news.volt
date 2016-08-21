<div class="alert alert-success">Последний коммит: <a href="{{ url('git/') }}">{{ lastCommit }}</a></div>

<table class="table">
	<tr>
		<td class="c" width="100">Дата</td>
		<td class="c">Новости</td>
	</tr>
	{% for news in parse %}
	<tr>
		<th width="70">{{ news[0] }}</th>
		<td style="text-align:left" class="b">{{ news[1] }}</td>
	</tr>
	{% endfor %}
</table>