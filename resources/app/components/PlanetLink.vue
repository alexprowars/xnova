<template>
	<Link :href="'/galaxy/?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet">
		[{{ galaxy }}:{{ system }}:{{ planet }}]
	</Link>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';

	defineProps({
		galaxy: {
			type: Number,
			default: 1
		},
		system: {
			type: Number,
			default: 1
		},
		planet: {
			type: Number,
			default: 1
		}
	});
</script>