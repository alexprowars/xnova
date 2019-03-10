<template>
	<table class="table">
		<tr>
			<td class="c" colspan="6">{{ page['title'] }}</td>
		</tr>
		<tr>
			<td class="c">&nbsp;</td>
			<td class="c">Пользователь</td>
			<td class="c">Альянс</td>
			<td class="c">Координаты</td>
			<td class="c">Текст</td>
			<td class="c">&nbsp;</td>
		</tr>
		<tr v-for="(list, id) in page['list']">
			<th width="20">
				{{ id + 1 }}
			</th>
			<th>
				<nuxt-link :to="'/messages/write/'+list['userid']+'/'">{{ list['username'] }}</nuxt-link>
			</th>
			<th v-html="list['ally']"></th>
			<th>
				<nuxt-link :to="'/galaxy/'+list['g']+'/'+list['s']+'/'">{{ list['g'] }}:{{ list['s'] }}:{{ list['p'] }}</nuxt-link>
			</th>
			<th>{{ list['online'] }}</th>
			<th>
				<nuxt-link v-if="page['isMy']" :to="'/buddy/delete/'+list['id']+'/'">Удалить запрос</nuxt-link>
				<template  v-else>
					<nuxt-link :to="'/buddy/approve/'+list['id']+'/'">Применить</nuxt-link>
					<br/>
					<nuxt-link :to="'/buddy/delete/'+list['id']+'/'">Отклонить</nuxt-link>
				</template>
			</th>
		</tr>
		<tr v-if="page['list'].length === 0">
			<th colspan="6">Нет запросов</th>
		</tr>
		<tr>
			<td colspan="6" class="c">
				<nuxt-link to="/buddy/">назад</nuxt-link>
			</td>
		</tr>
	</table>

</template>

<script>
	export default {
		name: "buddy-requests",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
	}
</script>