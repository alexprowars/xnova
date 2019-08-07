<template>
	<table class="table">
		<tr>
			<td class="c" colspan="2">Обзор заявок [{{ page['tag'] }}]</td>
		</tr>
		<tr v-if="page['request'] !== null">
			<td colspan="2" class="padding-0">
				<router-form :action="'/alliance/admin/edit/requests/show/'+page['request']['id']+'/'">
					<div class="separator"></div>
					<div class="table">
						<div class="row">
							<div class="col th">Заявка от {{ page['request']['username'] }}</div>
						</div>
						<div class="row">
							<div class="col th">{{ page['request']['request_text'] }}</div>
						</div>
						<div class="row">
							<div class="col c">Форма ответа:</div>
						</div>
						<div class="row">
							<div class="col th"><input type="submit" name="action" value="Принять"></div>
						</div>
						<div class="row">
							<div class="col th"><textarea name="text" cols=40 rows=10 title=""></textarea></div>
						</div>
						<div class="row">
							<div class="col th"><input type="submit" name="action" value="Отклонить"></div>
						</div>
					</div>
					<div class="separator"></div>
				</router-form>
			</td>
		</tr>
		<tr v-if="page['list'].length > 0">
			<td class="c text-center">
				<nuxt-link to="/alliance/admin/edit/requests/sort/1/">Логин</nuxt-link>
			</td>
			<td class="c text-center">
				<nuxt-link to="/alliance/admin/edit/requests/sort/0/">Дата подачи заявки</nuxt-link>
			</td>
		</tr>
		<tr v-for="list in page['list']">
			<th class="text-center">
				<nuxt-link :to="'/alliance/admin/edit/requests/show/'+list['id']+'/'">{{ list['username'] }}</nuxt-link>
			</th>
			<th class="text-center">
				{{ list['time'] }}
			</th>
		</tr>
		<tr v-if="page['list'].length === 0">
			<th colspan="2">Список заявок пуст</th>
		</tr>
		<tr>
			<td class="c" colspan="2"><nuxt-link to="/alliance/">Назад</nuxt-link></td>
		</tr>
	</table>
</template>

<script>
	export default {
		name: 'alliance-edit-requests',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>