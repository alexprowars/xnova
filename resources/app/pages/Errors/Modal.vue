<template>
	<Modal>
	<div class="block-table">
		<div class="grid">
			<div class="th error-message text-center" v-html="message"></div>
		</div>
	</div>
	</Modal>
</template>

<script setup>
	import { Modal } from '@inertiaui/modal-vue';

	defineProps({
		status: Number,
		message: String,
	});
</script>