<template>
	<div data-allow-mismatch>{{ $formatDate(now, 'DD MMM HH:mm:ss') }}</div>
</template>

<script setup>
	import { useNow } from '@vueuse/core';

	const now = useNow({ interval: 1000 });
</script>