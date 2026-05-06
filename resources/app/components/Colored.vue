<template>
	<span :class="style">
		{{ $formatNumber(value) }}
	</span>
</template>

<script setup>
	import { computed } from 'vue';

	const props = defineProps({
		value: {
			type: Number,
			default: 0,
		}
	});

	const style = computed(() => {
		return (props.value > 0 ? 'positive' : (props.value < 0 ? 'negative' : ''));
	});
</script>