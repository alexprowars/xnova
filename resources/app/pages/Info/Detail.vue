<template>
	<Head :title="page.item['name']"/>
	<InfoContent :item="page.item"/>
</template>

<script setup>
	import InfoContent from '~/components/Page/Info/Content.vue';
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