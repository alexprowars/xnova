<template>
	<div class="Toastify__toast-text text-center">
		<div v-if="contentProps.title" class="Toastify__toast-text-title">
			{{ contentProps.title }}
		</div>
		<div class="Toastify__toast-text-content" v-html="contentProps.content"></div>
	</div>
</template>

<script setup>
	defineProps({
		contentProps: {
			type: Object,
		},
	});

	defineOptions({
		inheritAttrs: false,
	})
</script>