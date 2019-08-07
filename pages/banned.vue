<template>
	<table class="table">
		<tr>
			<td class="c" colspan="6">Чёрный список</td>
		</tr>
		<tr v-if="page['items'].length === 0">
			<th class="b text-center" colspan="5">Нет заблокированных игроков</th>
		</tr>
		<template v-else>
			<tr>
				<th width="110">Логин</th>
				<th width="130">Дата блокировки</th>
				<th width="130">Конец блокировки</th>
				<th width="306">Причина блокировки</th>
				<th width="100">Модератор</th>
			</tr>
			<tr v-for="item in page['items']">
				<td class="b text-center">
					<nuxt-link :to="'/players/'+item['user']['id']+'/'">
						{{ item['user']['name'] }}
					</nuxt-link>
				</td>
				<td class="b text-center">
					<small>{{ item['time']|date('d.m.Y H:i:s') }}</small>
				</td>
				<td class="b text-center">
					<small>{{ item['time_end']|date('d.m.Y H:i:s') }}</small>
				</td>
				<td class="b text-center">{{ item['reason'] }}</td>
				<td class="b text-center">
					<nuxt-link :to="'/players/'+item['moderator']['id']+'/'">
						{{ item['moderator']['name'] }}
					</nuxt-link>
				</td>
			</tr>
			<tr>
				<td class="b text-center" colspan="5">Всего {{ page['items'].length }} аккаунтов заблокировано</td>
			</tr>
		</template>
	</table>
</template>

<script>
	export default {
		name: 'banned',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
	}
</script>