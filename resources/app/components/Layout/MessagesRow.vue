<template>
	<div class="information-message c" :class="[type.replace('-static', '')]" v-html="text"></div>
</template>

<script setup>
	defineProps({
		type: {
			type: String,
			default: '',
		},
		text: {
			type: String
		}
	})
</script>