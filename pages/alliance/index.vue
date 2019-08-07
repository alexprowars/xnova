<template>
	<table class="table" style="table-layout: fixed;">
		<tbody>
			<tr>
				<td class="c" colspan="2">Информация об альянсе</td>
			</tr>
			<tr v-if="page['image'] !== ''">
				<th colspan="2" class="p-a-0">
					<img :src="page['image']" style="max-width:100%" alt="">
				</th>
			</tr>
			<tr>
				<th>Аббревиатура</th>
				<th>{{ page['tag'] }}</th>
			</tr>
			<tr>
				<th>Название</th>
				<th>{{ page['name'] }}</th>
			</tr>
			<tr>
				<th>Члены альянса</th>
				<th>
					{{ page['members'] }}
					<template v-if="page['members_list']">
						(<nuxt-link :to="'/alliance/members/'">список</nuxt-link>)
					</template>
				</th>
			</tr>
			<tr>
				<th>Ваш ранг</th>
				<th>
					{{ page['range'] }}
					<template v-if="page['alliance_admin']">
						(<nuxt-link :to="'/alliance/admin/edit/ally/'">управление альянсом</nuxt-link>)
					</template>
				</th>
			</tr>
			<tr v-if="page['diplomacy'] !== false">
				<th>Дипломатия</th>
				<th>
					<nuxt-link :to="'/alliance/diplomacy/'">Просмотр</nuxt-link>
					<template v-if="page['diplomacy'] > 0">
						({{ page['diplomacy'] }} новых запросов)
					</template>
				</th>
			</tr>
			<tr v-if="page['requests'] > 0">
				<th>Заявки</th>
				<th>
					<nuxt-link :to="'/alliance/admin/edit/requests/'">{{ page['requests'] }} заявок</nuxt-link>
				</th>
			</tr>
			<tr v-if="page['chat_access']">
				<th>
					Альянс чат
					<template v-if="$store.state.user.alliance.messages > 0">
						({{ $store.state.user.alliance.messages }} новых)
					</template>
				</th>
				<th><nuxt-link :to="'/alliance/chat/'">Войти в чат</nuxt-link></th>
			</tr>
			<tr>
				<td class="b" colspan="2" height="100" style="padding:3px;">
					<text-viewer :text="page['description']"></text-viewer>
				</td>
			</tr>
			<tr v-if="page['web'] !== ''">
				<th>Домашняя страница</th>
				<th><a :href="page['web']" target="_blank">{{ page['web'] }}</a></th>
			</tr>
			<tr>
				<td class="c" colspan="2">Внутренняя компетенция</td>
			</tr>
			<tr>
				<td class="b" colspan="2" height="100" style="padding:3px;">
					<text-viewer :text="page['text']"></text-viewer>
				</td>
			</tr>
			<tr v-if="page['owner'] !== ''">
				<td colspan="2" v-html="page['owner']"></td>
			</tr>
		</tbody>
	</table>
</template>

<script>
	export default {
		name: 'alliance',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>