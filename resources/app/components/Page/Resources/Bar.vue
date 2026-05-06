<template>
	<div style="border: 1px solid #9999FF;">
		<div :style="{ backgroundColor: color, width: width + '%' }">&nbsp;</div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';

	const props = defineProps({
		value: {
			type: Number,
			default: 0
		},
		reverse: {
			type: Boolean,
			default: false
		}
	});

	const width = computed(() => {
		return Math.max(0, Math.min(100, props.value))
	});

	const color = computed(() => {
		let val = width.value;

		if (props.reverse)
			val = 100 - val

		if (val >= 100) {
			return '#C00000';
		} else if (val >= 80) {
			return '#C0C000';
		} else {
			return '#00C000';
		}
	});
</script>