<form action="{{ url('options/change/') }}" method="post">
	<table class="table">
		<tr>
			<td class="c" colspan="2">Режим отпуска</td>
		</tr>
		<tr>
		</tr>
		<tr>
			<th colspan="2">Режим отпуска включён до: <br/>{{ parse['um_end_date'] }}</th>
		</tr>
		<tr>
			<th>{{ _text('username') }}</th>
			<th><input name="db_character" size="20" value="{{ parse['opt_usern_data'] }}" type="hidden">{{ parse['opt_usern_data'] }}</th>
		</tr>
		<tr>
			<th><a title="{{ _text('vacations_tip') }}">{{ _text('mode_vacations') }}</a></th>
			<th><input name="urlaubs_modus"{{ parse['opt_modev_data'] }} type="checkbox" title=""></th>
		</tr>
		<tr>
			<th><a title="{{ _text('deleteaccount_tip') }}">{{ _text('deleteaccount') }}</a></th>
			<th><input name="db_deaktjava"{{ parse['opt_delac_data'] }} type="checkbox" title=""></th>
		</tr>
		<tr>
			<th colspan="2"><input type="submit" value="Сохранить изменения"/></th>
		</tr>
	</table>
</form>