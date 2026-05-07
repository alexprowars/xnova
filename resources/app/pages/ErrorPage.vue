<template>
	<div class="block-table">
		<div class="grid">
			<div class="th error-message text-center" v-html="message"></div>
		</div>
	</div>
</template>

<script setup>
	defineProps({
		status: Number,
		message: String,
	});
</script>