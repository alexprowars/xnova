<template>
	<table class="table">
		<tr>
			<th colspan="3" style="text-align:center;">Обновлено в {{ page['update']|date('H:i:s d.m.Y') }}</th>
		</tr>
		<template v-for="(list, group) in page['items']">
			<tr>
				<td class="c" style="width:199px">{{ group }}</td>
				<td class="c" style="width:203px">Игрок</td>
				<td class="c" style="width:172px">Уровень</td>
			</tr>
			<tr v-for="(info, building) in list">
				<th style="width:309px">{{ building }}</th>
				<th style="width:203px">{{ info['winner'] }}</th>
				<th style="width:82px">{{ info['count'] }}</th>
			</tr>
		</template>
	</table>
</template>

<script>
	export default {
		name: "records",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
	}
</script>