<template>
	<Modal>
		<Head :title="data.title"/>
		<div class="block">
			<div v-if="data.title" class="title" v-html="data.title"></div>
			<div class="content p-1" v-html="data.body"></div>
		</div>
	</Modal>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import { Modal } from '@inertiaui/modal-vue';

	const props = defineProps({
		data: Object,
	});
</script>