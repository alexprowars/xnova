<template>
	<div data-allow-mismatch>{{ $formatDate(now, 'DD MMM HH:mm:ss') }}</div>
</template>

<script setup>
	import { useUpdateInterval } from '~/composables/useUpdateInterval.js';

	const now = useUpdateInterval();
</script>