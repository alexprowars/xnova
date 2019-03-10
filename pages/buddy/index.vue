<template>
	<div>
		<table class="table">
			<tr>
				<td class="c" colspan="6">Список друзей</td>
			</tr>
			<tr>
				<th colspan="6"><nuxt-link to="/buddy/requests/">Запросы</nuxt-link></th>
			</tr>
			<tr>
				<th colspan="6"><nuxt-link to="/buddy/requests/my/">Мои запросы</nuxt-link></th>
			</tr>
			<tr>
				<td class="c">&nbsp;</td>
				<td class="c">Имя</td>
				<td class="c">Альянс</td>
				<td class="c">Координаты</td>
				<td class="c">Позиция</td>
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
				<th v-html="list['online']"></th>
				<th>
					<nuxt-link :to="'/buddy/delete/'+list['id']+'/'">Удалить</nuxt-link>
				</th>
			</tr>
			<tr v-if="page['list'].length === 0">
				<th colspan="6">Нет друзей</th>
			</tr>
		</table>
	</div>
</template>

<script>
	export default {
		name: "buddy",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
	}
</script>