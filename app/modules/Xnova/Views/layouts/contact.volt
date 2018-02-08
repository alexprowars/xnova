<table class="table">
	<tr>
		<td colspan="3" class="c"><b>Начальство</b></td>
	</tr>
	<tr>
		<th colspan="3">
			<font color="orange">Здесь вы найдёте адреса всех администраторов и операторов игры для обратной связи</font>
		</th>
	</tr>
	<tr>
		<th width="33%">Имя</th>
		<th width="33%">Должность</th>
		<th width="33%">eMail</th>
	</tr>
	{% for list in contacts %}
		<tr>
			<th>{{ list['name'] }}</th>
			<th>{{ list['auth'] }}</th>
			<th><a href=mailto:{{ list['mail'] }}>{{ list['mail'] }}</a></th>
		</tr>
		<tr>
			<td class="c" colspan="3">
				<text-viewer text="{{ list['info'] }}"></text-viewer>
			</td>
		</tr>
	{% endfor %}
</table>