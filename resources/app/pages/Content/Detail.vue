<template>
	<Head :title="page.title"/>
	<ContentBody :title="page.title" :body="page.body"/>
</template>

<script setup>
	import ContentBody from '~/components/Page/Content/Body.vue';
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	});
</script>