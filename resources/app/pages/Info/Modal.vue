<template>
	<Modal>
		<Head :title="item['name']"/>
		<InfoContent :item="item"/>
	</Modal>
</template>

<script setup>
	import InfoContent from '~/components/Page/Info/Content.vue';
	import { Head } from '@inertiajs/vue3';
	import { Modal } from '@inertiaui/modal-vue';

	defineProps({
		item: {
			type: Object,
		}
	});
</script>