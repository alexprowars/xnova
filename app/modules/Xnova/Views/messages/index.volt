<form action="{{ url('messages/') }}" id="mes_form" method="post">
	<input name="category" value="{{ parse['category'] }}" type="hidden">
	<div class="block">
		<div class="title">
			Сообщения
			<select name="messcat" onChange="$('#mes_form').submit()" title="">
				{% for type in parse['types'] %}
					<option value="{{ type }}" {{ type == parse['category'] ? 'selected' : '' }}>{{ _text('xnova', 'type', type) }}</option>
				{% endfor %}
			</select>
			по
			<select name="show_by" onChange="$('#mes_form').submit()" title="">
				{% for limit in parse['limit'] %}
					<option value="{{ limit }}" {{ limit == parse['lim'] ? 'selected' : '' }}>{{ limit }}</option>
				{% endfor %}
			</select>
			на странице
			<div style="float: right">
				<input name="deletemessages" value="Удалить отмеченные" type="submit">
			</div>
		</div>
		<div class="content noborder">
			<table class="table">
				<tr>
					<th width="50"><input type="checkbox" class="checkAll" style='width:14px;' title=""></th>
					<th width="150">Дата</th>
					<th>От</th>
					<th width="65">&nbsp;</th>
				</tr>
				{% for item in page.items %}
					<tr>
						<th>
							<input name="delete[]" type="checkbox" value="{{ item.id }}" style='width:14px;' title="">
						</th>
						<th>{{ game.datezone("d.m.y H:i:s", item.time) }}</th>
						<th>
							{% if item.sender > 0 %}
								<a href="{{ url('players/'~item.sender~'/') }}" class="window popup-user">{{ item.from }}</a>
							{% else %}
								{{ item.from }}
							{% endif %}
						</th>
						<th nowrap>
							{% if item.type == 1 %}
								<a href="{{ url('messages/write/'~item.sender~'/') }}" title="Ответить"><span class='sprite skin_m'></span></a>
								&nbsp;<a href="{{ url('messages/write/'~item.sender~'/quote/'~item.id~'/') }}" title='Цитировать сообщение'><span class='sprite skin_z'></span></a>
								&nbsp;<a href="javascript:;" onclick='window.confirm("Вы уверены что хотите отправить жалобу на это сообщение?") ? window.location.href="{{ url('messages/abuse/'~item.id~'/') }}" : false;' title='Отправить жалобу'><span class='sprite skin_s'></span></a>
							{% else %}
								&nbsp;
							{% endif %}
						</th>
					</tr>
					<tr>
						<td style="background-color:{{ _text('xnova', 'mess_background', item.type) }};" colspan="4" class="b">
							{% if item.type == 1 and user.getUserOption('bb_parser') %}
								<span id="m{{ item.id }}"></span>
								<script type="text/javascript">Text('{{ replace(["\r\n", "\n", "\r"], '<br>', replace('#BASEPATH#', url.getBaseUri(), item.text)|stripslashes) }}', 'm{{ item.id }}');</script>
							{% else %}
								{{ (replace('#BASEPATH#', url.getBaseUri(), item.text)|nl2br)|stripslashes }}
							{% endif %}
						</td>
					</tr>
				{% endfor %}
				{% if page.total_items == 0 %}
					<tr>
						<th colspan="4" align="center">нет сообщений</th>
					</tr>
				{% endif %}
			</table>
			<div style="float: left">
				{{ parse['pages'] }}
			</div>
			<div style="float: right;padding: 5px">
				<input name="deletemessages" value="Удалить отмеченные" type="submit">
			</div>
		</div>
	</div>
</form>