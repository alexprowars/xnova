<style>.image {
	max-width: 556px !important
}</style>
<form action="{{ url('alliance/chat/') }}" method="post">
	<div class="table">
		<div class="row">
			<div class='col-xs-12 c'><a href="{{ url('alliance/chat/') }}">Обновить</a></div>
		</div>

		{% if parse['messages']|length > 0 %}
			{% for m in parse['messages'] %}
				<div class="row">
					<div class="col-xs-12 j p-a-0">
						<table width="100%">
							<tr>
								<th class="b text-xs-center" width="130">
									{{ game.datezone("H:i:s", m['timestamp']) }}<br><a href="{{ url('players/'~m['user_id']~'/') }}" target="_blank">{{ m['user']|stripslashes }}</a>
									<a onclick="AddQuote('{{ m['user']|stripslashes }}', 'm{{ m['id'] }}')"> -> </a>
								</th>
								<th class="b">
									{% if parse['parser'] %}
										<span id="m{{ m['id'] }}"></span>
									{% else %}
										{{ replace(["\r\n", "\n", "\r"], '', m['message']|stripslashes) }}
									{% endif %}
								</th>
								{% if parse['owner'] %}
									<th class="b" width="20"><input name="showmes{{ m['id'] }}" type="hidden" value="1"><input name="delmes{{ m['id'] }}" type="checkbox" title=""></th>
								{% endif %}
							</tr>
						</table>
					</div>
				</div>
			{% endfor %}
		{% else %}
			<div class="row">
				<div class="col-xs-12 b" align="center">В альянсе нет сообщений.</div>
			</div>
		{% endif %}

		<div class="row">
			<div class="col-xs-12 th">{{ parse['pages'] }}</div>
		</div>

		{% if parse['owner'] and parse['messages']|length > 0 %}
			<div class="row">
				<div class="col-xs-12 th">
					<select id="deletemessages" name="deletemessages" title="">
						<option value="deletemarked">Удалить выделенные</option>
						<option value="deleteunmarked">Удалить не выделенные</option>
						<option value="deleteall">Удалить все</option>
					</select>
					<input value="Удалить" type="submit">
				</div>
			</div>
		{% endif %}
	</div>
</form>
<div class="separator"></div>
<script type="text/javascript">
	var messages = new Array(20);
	{% if parse['messages']|length > 0): for ($parse['messages'] AS $m %}
		messages['m{{ m['id'] }}'] = '<?=str_replace(["\r\n", "\n", "\r"], '', addslashes(stripslashes($m['message']))) ?>';
	<? endfor;  endif; ?>
	{% if parse['parser'] %}
		$(document).ready(function(){ShowText()});
	{% endif %}
</script>
<form action="{{ url('alliance/chat/') }}" method="post">
	<table class="table">
		<tr>
			<td class="c">Отправить сообщение в чат альянса</td>
		</tr>
		<tr>
			<th class="p-a-0">
				<div id="editor"></div>
				<textarea name="text" id="text" rows="10" onkeypress="if ((event.ctrlKey) && ((event.keyCode == 10) || (event.keyCode == 13))) submit()" title=""></textarea>
			</th>
		</tr>
		<tr>
			<td class="c">
				<input type="reset" value="Очистить">
				<input type="submit" value="Отправить">
			</td>
		</tr>
	</table>
	<div id="showpanel" style="display:none">
		<table class="table">
			<tr>
				<td class="c"><b>Предварительный просмотр</b></td>
			</tr>
			<tr>
				<td class="b"><span id="showbox"></span></td>
			</tr>
		</table>
	</div>
</form>
<span style="float:left;margin-left:10px;margin-top:7px;"><a href="{{ url('alliance/') }}">[назад к альянсу]</a></span>
<script type="text/javascript">edToolbar('text');</script>