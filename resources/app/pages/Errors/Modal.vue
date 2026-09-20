<template>
	<Modal>

	<div class="game-page page-error"><section class="error-notice" role="alert"><AlertIcon aria-hidden="true"/><div class="game-prose" v-html="page.message"></div></section></div>
	</Modal>
</template>

<script setup>
	import AlertIcon from '~/images/icons/alert.svg?component';
	import { Modal } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});
</script>