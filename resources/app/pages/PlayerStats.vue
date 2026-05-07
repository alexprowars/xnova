<template>
	<Head :title="$t('pages.players.meta_stats_title')"/>
	<Stats type="user" :data="data"/>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import Stats from '../components/Page/Players/Stats.vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		data: {
			type: Object,
		}
	});
</script>