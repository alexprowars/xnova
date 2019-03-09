<template>
	<div class="table overview-fleets">
		<fleets-row v-for="(item, i) in items" :key="i" :item="item"></fleets-row>
	</div>
</template>

<script>
	import FleetsRow from './fleets-row.vue'

	export default {
		name: "overview-fleets",
		props: ['items'],
		components: {
			FleetsRow
		},
	}
</script>