<template>
	<div class="block-table">
		<div class="grid" v-if="item['title'] && item['title'].length">
			<div class="c error" v-html="item['title']"></div>
		</div>
		<div class="grid">
			<div class="th error-message text-center" v-html="item['message']"></div>
		</div>
	</div>
</template>

<script setup>
	import { onBeforeUnmount, onMounted } from 'vue';
	import { router } from '@inertiajs/vue3';

	const props = defineProps({
		item: Object,
	});

	let timeout;

	onMounted(() => {
		if (props.item['timeout'] > 0 && props.item['redirect']) {
			timeout = setTimeout(() => {
				router.visit(props.item['redirect'])
			}, props.item['timeout'] * 1000);
		}
	});

	onBeforeUnmount(() => {
		clearTimeout(timeout);
	});
</script>