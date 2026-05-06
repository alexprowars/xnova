<template>
	<a :href="'/info/' + id" @click.prevent="openPopup">
		<slot></slot>
	</a>
</template>

<script setup>
	import InfoContent from './Content.vue';
	import { useWithLoadngIndicator } from '../../../composables/useLoading.js';
	import { openPopupModal } from '../../../composables/useModals.js';
	import { useApiGet } from '../../../composables/useApi.js';

	const props = defineProps({
		id: {
			type: Number,
			default: 0,
		}
	})

	function openPopup () {
		if (props.id <= 0) {
			return;
		}

		useWithLoadngIndicator(async () => {
			const result = await useApiGet('/info/' + props.id);

			await openPopupModal(InfoContent, { item: result });
		});
	}
</script>