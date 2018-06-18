<template>
	<div class="table overview-fleets">
		<fleets-row v-for="item in items" :key="item['id']" :item="item"></fleets-row>
	</div>
</template>

<script>
	import FleetsRow from './overview-fleets-row.vue'

	export default {
		name: "overview-fleets",
		props: ['items'],
		components: {
			FleetsRow
		},
	}
</script>