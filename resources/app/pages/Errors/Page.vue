<template>
	<div class="game-page page-error"><section class="error-notice" role="alert"><AlertIcon aria-hidden="true"/><div class="game-prose" v-html="page.message"></div></section></div>
</template>

<script setup>
	import AlertIcon from '~/images/icons/alert.svg?component';
	const props = defineProps({
		page: Object,
	});
</script>