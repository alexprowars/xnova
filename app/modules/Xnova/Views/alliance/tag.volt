<router-form action="{{ url('alliance/admin/edit/tag/') }}">
	<div class="block">
		<div class="title">Введите новую аббревиатуру альянса</div>
		<div class="content table border-0 middle">
			<div class="row">
				<div class="col th">
					<input type="text" name="tag" value="{{ tag }}">
					<input type="submit" value="Изменить">
				</div>
			</div>
			<div class="row c">
				<router-link to="{{ url('alliance/admin/edit/ally/') }}">вернутся к обзору</router-link>
			</div>
		</div>
	</div>
</router-form>