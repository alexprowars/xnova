<template>
	<Head title="Статистика альянса"/>
	<Stats type="alliance" :data="page"/>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import Stats from '~/components/Page/Players/Stats.vue';

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