<template>
	<Head title="Статистика альянса"/>
	<Stats type="alliance" :data="data"/>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import Stats from '../../components/Page/Players/Stats.vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		data: Object,
	});
</script>