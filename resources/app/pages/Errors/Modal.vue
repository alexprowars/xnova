<template>
	<Modal>
	<div class="block-table">
		<div class="grid">
			<div class="th error-message text-center" v-html="page.message"></div>
		</div>
	</div>
	</Modal>
</template>

<script setup>
	import { Modal } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});
</script>