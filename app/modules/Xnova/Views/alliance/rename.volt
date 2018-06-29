<router-form action="{{ url('alliance/admin/edit/'~parse['form']~'/') }}">
	<table class="table">
		<tr>
			<td class="c">{{ parse['question'] }}</td>
		</tr>
		<tr>
			<th><input type="text" name="{{ parse['name'] }}" title=""> <input type="submit" value="Изменить" title=""></th>
		</tr>
		<tr>
			<td class="c"><router-link to="{{ url('alliance/admin/edit/ally/') }}">вернутся к обзору</router-link></td>
		</tr>
	</table>
</router-form>