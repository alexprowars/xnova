<template>
	<table class="table">
		<tr is="game-page-overview-fleets-row" v-for="item in items" v-bind:item="item"></tr>
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

<style scoped>

</style>