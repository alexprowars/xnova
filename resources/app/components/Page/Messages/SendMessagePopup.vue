<template>
	<a href="" @click.prevent="openPopup">
		<span class="sprite skin_m"></span>
	</a>
</template>

<script setup>
	import MessageForm from '../../../components/Page/Messages/Form.vue';
	import { openPopupModal } from '../../../composables/useModals.js';
	import { progress } from '@inertiajs/vue3';
	import { useApiGet } from '../../../composables/useApi.js';

	const props = defineProps({
		id: Number,
	});

	async function openPopup() {
		progress.start();

		const { id, to, message } = await useApiGet('/messages/write/' + props.id);

		progress.finish();

		await openPopupModal(MessageForm, { id, to, message });
	}
</script>