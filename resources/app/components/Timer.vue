<template>
	<div>{{ $formatTime(time, delimiter, true) }}</div>
</template>

<script setup>
	import dayjs from 'dayjs';
	import { useNow } from '@vueuse/core';
	import { computed } from 'vue';

	const props = defineProps({
		value: {
			type: String,
			default: 0,
		},
		delimiter: {
			type: String,
			default: ':',
		}
	});

	const now = useNow({ interval: 1000 });
	const time = computed(() => dayjs(props.value).diff(now.value) / 1000);
</script>