<template>
	<Head :title="data.title"/>
	<div v-html="data.html"></div>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		data: Object,
	});
</script>