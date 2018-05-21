<template>
	<div class="table overview-fleets">
		<game-page-overview-fleets-row v-for="item in items" :item="item"></game-page-overview-fleets-row>
	</div>
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