<template>
	<Modal>
	<Head :title="page.title"/>
	<ContentBody :title="page.title" :body="page.body"/>
	</Modal>
</template>

<script setup>
	import ContentBody from '~/components/Page/Content/Body.vue';
	import { Head } from '@inertiajs/vue3';
	import { Modal } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});
</script>