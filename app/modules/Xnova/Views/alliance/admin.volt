<div class="container-fluid">
	<table class="table">
		<tr>
			<td class="c">Управление альянсом</td>
		</tr>
		{% if user.isAdmin() and parse['owner'] == userId %}
			<tr>
				<th><a href="{{ url('alliance/admin/edit/planets/') }}">Планеты альянса</a></th>
			</tr>
		{% endif %}
		<tr>
			<th><a href="{{ url('alliance/admin/edit/rights/') }}">Установить ранги</a></th>
		</tr>
		{% if parse['can_view_members'] %}
			<tr>
				<th><a href="{{ url('alliance/admin/edit/members/') }}">Члены альянса</a></th>
			</tr>
		{% endif %}
		<tr>
			<th><a href="{{ url('alliance/admin/edit/tag/') }}">Изменить аббревиатуру альянса</a></th>
		</tr>
		<tr>
			<th><a href="{{ url('alliance/admin/edit/name/') }}">Изменить название альянса</a></th>
		</tr>
	</table>

	<form action="{{ url('alliance/admin/edit/ally/t/'~parse['t']~'/') }}" method="POST">
		<input type="hidden" name="t" value="{{ parse['t'] }}">
		<table class="table">
			<tr>
				<td class="c" colspan="3">{{ _text('xnova', 'Texts') }}</td>
			</tr>
			<tr>
				<th><a href="{{ url('alliance/admin/edit/ally/t/1/') }}">Внешний текст</a></th>
				<th><a href="{{ url('alliance/admin/edit/ally/t/2/') }}">Внутренний текст</a></th>
				<th><a href="{{ url('alliance/admin/edit/ally/t/3/') }}">Текст заявки</a></th>
			</tr>
			<tr>
				<td class="c" colspan="3">{{ _text('xnova', 'Show_of_request_text') }}</td>
			</tr>
			<tr>
				<th colspan="3" class="p-a-0">
					<div id="editor"></div>
					<script type="text/javascript">edToolbar('text');</script>
					<textarea name="text" id="text" rows="15" title="">{{ preg_replace('!<br.*>!iU', "\n", parse['text']) }}</textarea>
				</th>
			</tr>
			<tr>
				<th colspan="3"><input type="reset" value="Очистить"><input type="submit" value="Сохранить"></th>
			</tr>
		</table>
		<div id="showpanel" style="display:none">
			<table align="center" class="table">
				<tr>
					<td class="c"><b>Предварительный просмотр</b></td>
				</tr>
				<tr>
					<td class="b" style="padding:3px;"><span id="showbox"></span></td>
				</tr>
			</table>
		</div>
	</form>
	<div class="separator"></div>
	<form action="{{ url('alliance/admin/edit/ally/') }}" method="POST">
		<table class="table">
			<tr>
				<td class="c" colspan="2">Дополнительные настройки</td>
			</tr>
			<tr>
				<th width="150">Домашняя страница</th>
				<th><input type="text" name="web" value="{{ parse['web'] }}" style="width:98%;" title=""></th>
			</tr>
			<tr>
				<th>Логотип</th>
				<th><input type="text" name="image" value="{{ parse['image'] }}" style="width:98%;" title=""></th>
			</tr>
			<tr>
				<th>Ранг основателя</th>
				<th><input type="text" name="owner_range" value="{{ parse['owner_range'] }}" style="width:98%;" title=""></th>
			</tr>
			<tr>
				<th>Заявки</th>
				<th>
					<select style="width:98%;" name="request_notallow" title="">
						<option value="1"{{ parse['request_notallow_0'] }}>{{ _text('xnova', 'No_allow_request') }}</option>
						<option value="0"{{ parse['request_notallow_1'] }}>{{ _text('xnova', 'Allow_request') }}</option>
					</select>
				</th>
			</tr>
			<tr>
				<th colspan="2"><input type="submit" name="options" value="Сохранить"></th>
			</tr>
		</table>
	</form>

	<div class="separator"></div>
	<div class="row">
		{% if parse['Disolve_alliance'] is defined %}
			<div class="col-6">
				{{ parse['Disolve_alliance'] }}
			</div>
		{% endif %}
		{% if parse['Transfer_alliance'] is defined %}
			<div class="col-6">
				{{ parse['Transfer_alliance'] }}
			</div>
		{% endif %}
	</div>
</div>