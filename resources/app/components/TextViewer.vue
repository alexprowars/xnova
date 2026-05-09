<template>
	<div v-html="parsed"></div>
</template>

<script setup>
	import parser from '~/utils/parser';
	import { computed } from 'vue';

	const props = defineProps({
		text: {
			type: String,
			default: '',
		}
	});

	const parsed = computed(() => {
		return parser.parse(props.text);
	});
</script>