<template>
	<Head :title="$t('pages.players.meta_ally_stats_title')"/>
	<div class="page-alliance ">
		<AllianceBack/>
		<Stats type="alliance" :data="page"/>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
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