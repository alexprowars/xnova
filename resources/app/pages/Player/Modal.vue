<template>
	<Modal>
		<Head :title="$t('pages.players.meta_info_title')" />
		<PlayerInfo :item="page"/>
	</Modal>
</template>

<script setup>
	import PlayerInfo from '~/components/Page/Players/Info.vue';
	import { Head } from '@inertiajs/vue3';
	import { Modal } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});
</script>
