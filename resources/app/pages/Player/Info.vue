<template>
	<Head :title="$t('pages.players.meta_info_title')" />
	<PlayerInfo :item="page"/>
</template>

<script setup>
	import PlayerInfo from '~/components/Page/Players/Info.vue';
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
