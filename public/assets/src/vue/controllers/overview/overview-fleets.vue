<template>
	<table class="table">
		<tr is="game-page-overview-fleets-row" v-for="item in items" :item="item"></tr>
	</table>
</template>

<script>
	export default {
		name: "overview-fleets",
		props: ['items'],
		components: {
			'game-page-overview-fleets-row': require('./overview-fleets-row.vue'),
		},
	}
</script>