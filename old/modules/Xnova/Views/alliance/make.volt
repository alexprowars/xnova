{% if html is defined %}
	{{ html }}
{% else %}
	<router-form action="{{ url('alliance/make/yes/1/') }}">
		<table class="table">
			<tr>
				<td class="c" colspan="2">Создать альянс</td>
			</tr>
			<tr>
				<th>Аббревиатура альянса (3-8 символов)</th>
				<th><input type="text" name="atag" size=8 maxlength=8 value="" title=""></th>
			</tr>
			<tr>
				<th>Название альянса (max. 35 символов)</th>
				<th><input type="text" name="aname" size=20 maxlength=30 value="" title=""></th>
			</tr>
			<tr>
				<th colspan="2"><input type="submit" value="Создать"></th>
			</tr>
		</table>
	</router-form>
{% endif %}