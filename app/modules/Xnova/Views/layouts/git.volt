<table class="table">
	<tr>
		<td class="c" width="100">Дата</td>
		<td class="c">Новости</td>
	</tr>
	{% for hash, news in history %}
	<tr>
		<th width="70">{{ news['date'] }}</th>
		<td style="text-align:left" class="b">
			<div class="positive">{{ news['author'] }}</div>
			<br>
			{{ news['message'] }}
			<br>
			Commit hash <a href="https://gitlab.com/alexprowars/xnova-uni5/commit/{{ hash }}/" target="_blank">{{ hash }}</a>
		</td>
	</tr>
	{% endfor %}
</table>