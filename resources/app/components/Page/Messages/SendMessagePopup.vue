<template>
	<ModalLink navigate :href="'/messages/write/' + props.id" :aria-label="$t('send_message')">
		<slot><SendIcon class="inline-block size-4 align-middle" aria-hidden="true" focusable="false"/></slot>
	</ModalLink>
</template>

<script setup>
	import SendIcon from '~/images/icons/send.svg?component';
	import { ModalLink } from '@inertiaui/modal-vue';

	const props = defineProps({
		id: Number,
	});
</script>