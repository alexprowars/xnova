<template>
	<div class="resources-bar" role="progressbar" :aria-valuenow="width" :aria-valuemin="0" :aria-valuemax="100" :style="{ '--bar-color': color }">
		<span :style="{ width: width + '%' }"></span>
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
			return '#dd928b';
		} else if (val >= 80) {
			return '#d8b57a';
		} else {
			return '#82c7b0';
		}
	});
</script>