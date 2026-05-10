<template>
	<div class="block-table">
		<div class="grid">
			<div class="th error-message text-center" v-html="page.message"></div>
		</div>
	</div>
</template>

<script setup>
	const props = defineProps({
		page: Object,
	});
</script>