<template>
	<div class="block-table overview-fleets">
		<FleetsRow v-for="(item, i) in items" :key="i" :item="item"/>
	</div>
</template>

<script setup>
	import FleetsRow from './FleetsRow.vue';

	defineProps({
		items: Array,
	});
</script>