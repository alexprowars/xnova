<template>
	<Head :title="item['name']"/>
	<InfoContent :item="item"/>
</template>

<script setup>
	import InfoContent from '../components/Page/Info/Content.vue';
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		item: {
			type: Object,
		}
	});
</script>