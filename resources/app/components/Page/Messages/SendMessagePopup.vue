<template>
	<a href="" @click.prevent="openPopup">
		<span class="sprite skin_m"></span>
	</a>
</template>

<script setup>
	import MessageForm from '../../../components/Page/Messages/Form.vue';
	import { openPopupModal } from '../../../composables/useModals.js';

	const props = defineProps({
		id: Number,
	});

	async function openPopup() {
		const { start, finish } = useLoadingIndicator();

		start();

		const { id, to, message } = await useApiGet('/messages/write/' + props.id);

		finish();

		await openPopupModal(MessageForm, { id, to, message });
	}
</script>