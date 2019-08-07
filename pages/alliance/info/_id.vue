<template>
	<table class="table" style="table-layout: fixed;">
		<tr>
			<td class="c" colspan="2">Информация об альянсе</td>
		</tr>
		<tr v-if="page['image'] !== ''">
			<th colspan="2"><img :src="page['image']" style="max-width:100%" alt=""></th>
		</tr>
		<tr>
			<th>Аббревиатура</th>
			<th>{{ page['tag'] }}</th>
		</tr>
		<tr>
			<th>Имя</th>
			<th>{{ page['name'] }}</th>
		</tr>
		<tr>
			<th>Участники</th>
			<th>{{ page['member_scount'] }}</th>
		</tr>
		<tr v-if="page['description'] !== ''">
			<td class="b" colspan="2" height="100" style="padding:3px;">
				<text-viewer :text="page['description']"></text-viewer>
			</td>
		</tr>
		<tr v-if="page['web'] !== ''">
			<th>Сайт альянса:</th>
			<th><a :href="page['web']" target="_blank">{{ page['web'] }}</a></th>
		</tr>
		<tr v-if="page['request']">
			<th colspan="2">
				<nuxt-link :to="'/alliance/apply/allyid/'+page['id']+'/'" class="button">Вступить в альянс</nuxt-link>
			</th>
		</tr>
	</table>
</template>

<script>
	export default {
		name: 'alliance-info',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>