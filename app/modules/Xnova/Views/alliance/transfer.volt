<router-form action="{{ url('alliance/admin/edit/give/id/'~parse['id']~'/') }}">
	<table class="table">
		<tr>
			<td class="c" colspan="8">Передача альянса</td>
		</tr>
			<tr>
				<th colspan="3">Передать альянс игроку:</th>
				<th><select name="newleader" title="">{{ parse['righthand'] }}</select></th>
				<th colspan="3"><input type="submit" value="Передача"></th>
			</tr>
		<tr>
			<td class="c" colspan="8"><router-link to="{{ url('alliance/admin/edit/ally/') }}">назад</router-link></td>
		</tr>
	</table>
</router-form>