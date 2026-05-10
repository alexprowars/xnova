<template>
	<Modal>
		<Head :title="page.title"/>
		<div class="block">
			<div v-if="page.title" class="title" v-html="page.title"></div>
			<div class="content p-1" v-html="page.body"></div>
		</div>
	</Modal>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import { Modal } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});
</script>