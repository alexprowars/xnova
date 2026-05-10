<template>
	<Modal>
		<Head :title="page.item['name']"/>
		<InfoContent :item="page.item"/>
	</Modal>
</template>

<script setup>
	import InfoContent from '~/components/Page/Info/Content.vue';
	import { Head } from '@inertiajs/vue3';
	import { Modal } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});
</script>