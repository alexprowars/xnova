<template>
	<ModalLink navigate :href="'/messages/write/' + props.id">
		<span class="sprite skin_m"></span>
	</ModalLink>
</template>

<script setup>
	import { ModalLink } from '@inertiaui/modal-vue';

	const props = defineProps({
		id: Number,
	});
</script>