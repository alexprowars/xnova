<template>
	<div>
	<router-form action="/alliance/admin/edit/rights/">
		<table class="table">
			<tr>
				<td class="c" colspan="13">Установить ранги</td>
			</tr>
			<tr>
				<th colspan="2">Имя ранга</th>
				<th><img src="/images/alliance/r1.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r2.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r3.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r4.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r5.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r6.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r7.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r8.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r9.png" width="16" alt=""></th>
				<th><img src="/images/alliance/r10.gif" width="16" alt=""></th>
			</tr>
			<tr v-for="r in page['list']">
				<th>{{ r['delete'] }}<input type="hidden" name="id[]" :value="r['a']"></th>
				<th>&nbsp;{{ r['r0'] }}&nbsp;</th>
				<th>{{ r['r1'] }}</th>
				<th>{{ r['r2'] }}</th>
				<th>{{ r['r3'] }}</th>
				<th>{{ r['r4'] }}</th>
				<th>{{ r['r5'] }}</th>
				<th>{{ r['r6'] }}</th>
				<th>{{ r['r7'] }}</th>
				<th>{{ r['r8'] }}</th>
				<th>{{ r['r9'] }}</th>
				<th>{{ r['r10'] }}</th>
			</tr>
			<tr v-if="page['list'].length > 0">
				<th colspan="13"><input type="submit" value="Сохранить"></th>
			</tr>
			<tr v-if="page['list'].length === 0">
				<th colspan="13" align="center">нет рангов</th>
			</tr>
		</table>
	</router-form>
	<div class="separator"></div>
	<router-form action="/alliance/admin/edit/rights/add/name/">
		<table class="table">
			<tr>
				<td class="c" colspan="2">Создать новый ранг</td>
			</tr>
			<tr>
				<th>Имя ранга</th>
				<th><input type="text" name="newrangname" size="20" maxlength="30" title=""></th>
			</tr>
			<tr>
				<th colspan="2"><input type="submit" value="Создать"></th>
			</tr>
		</table>
	</router-form>
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="2">Описание Законов</td>
		</tr>
		<tr>
			<th><img src="/images/alliance/r1.png" alt=""></th>
			<th>Расформировать альянс</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r2.png" alt=""></th>
			<th>Редактирование участников</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r3.png" alt=""></th>
			<th>Показать заявки</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r4.png" alt=""></th>
			<th>Показать список участников</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r5.png" alt=""></th>
			<th>Проверить запросы</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r6.png" alt=""></th>
			<th>Управление альянсом</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r7.png" alt=""></th>
			<th>Показать онлайн статус в списке участников</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r8.png" alt=""></th>
			<th>Написать сообщение в чат</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r9.png" alt=""></th>
			<th>'Правая Рука' (для передачи необходим ранг основателя)</th>
		</tr>
		<tr>
			<th><img src="/images/alliance/r10.gif" alt=""></th>
			<th>Дипломатия</th>
		</tr>
		<tr>
			<td class="c" colspan="2">
				<nuxt-link to="/alliance/admin/edit/ally/">вернутся к обзору</nuxt-link>
			</td>
		</tr>
	</table>
	</div>
</template>

<script>
	export default {
		name: 'alliance-edit-rights',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>