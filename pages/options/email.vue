<template>
	<router-form action="/options/email/">
		<table class="table">
			<tr>
				<td class="c" colspan="2">Смена адреса электронной почты</td>
			</tr>
			<tr>
				<th>Текущий пароль</th>
				<th><input name="password" size="20" value="" type="password" title=""></th>
			</tr>
			<tr>
				<th>Новый EMAIL адрес</th>
				<th><input name="email" size="20" value="" type="text" title=""></th>
			</tr>
			<tr>
				<th colspan="2">
					Смена адреса email осуществляется ручным методом после проверки аккаунта администрацией игры.
				</th>
			</tr>
			<tr>
				<td colspan="2" class="c"><input type="submit" value="Отправить на модерацию"></td>
			</tr>
		</table>
	</router-form>
</template>

<script>
	export default {
		name: 'options-email'
	}
</script>