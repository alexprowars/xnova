<template>
	<div class="table">
		<div class="row">
			<div class="col th">Обновлено в {{ page['update']|date('H:i:s d.m.Y') }}</div>
		</div>
		<template v-for="(list, group) in page['items']">
			<div class="row">
				<div class="col c">{{ group }}</div>
				<div class="col c">Игрок</div>
				<div class="col-2 c">Уровень</div>
			</div>
			<div v-for="(info, building) in list" class="row">
				<div class="col th">{{ building }}</div>
				<div class="col th">{{ info['winner'] }}</div>
				<div class="col-2 th">{{ info['count'] }}</div>
			</div>
		</template>
	</div>
</template>

<script>
	export default {
		name: 'records',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
	}
</script>