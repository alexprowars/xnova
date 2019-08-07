<template>
	<div>
		<router-form action="/notes/new/">
			<table class="table">
				<tr>
					<td class="c" colspan="2">Создание заметки</td>
				</tr>
				<tr>
					<th>Приоритет:
						<select name="u" title="">
							<option value="2" selected>Важно</option>
							<option value="1">Обычно</option>
							<option value="0">Неважно</option>
						</select>
					</th>
					<th>Тема:
						<input type="text" name="title" size="30" maxlength="30" value="" placeholder="Введите тему">
					</th>
				</tr>
				<tr>
					<th colspan="2" class="p-a-0">
						<text-editor></text-editor>
					</th>
				</tr>
				<tr>
					<td class="c" colspan="2">
						<input type="reset" value="Сброс">
						<input type="submit" value="Сохранить">
					</td>
				</tr>
			</table>
		</router-form>
		<span style="float:left;margin-left: 10px;margin-top: 10px;"><router-link to="/notes/">Назад</router-link></span>
	</div>
</template>

<script>
	export default {
		name: 'notes-new',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>