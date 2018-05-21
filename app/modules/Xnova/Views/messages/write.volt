{% if msg is defined %}
	{{ msg }}
{% endif %}
<form action="{{ url('messages/write/'~id~'/') }}" method="post" {% if isPopup %}class="popup"{% endif %}>
	<table class="table form-group">
		{% if isPopup is false %}
		<tr>
			<td class="c" colspan="2">Отправка сообщения</td>
		</tr>
		{% endif %}
		<tr>
			<th>Получатель: <input type="text" name="to" id="to" style="width: 100%" value="{{ to }}"  title=""></th>
		</tr>
		<tr>
			<th class="p-a-0">
				<text-editor text="{{ text }}"></text-editor>
			</th>
		</tr>
		<tr>
			<th colspan="2"><input type="submit" value="Отправить"></th>
		</tr>
	</table>
</form>