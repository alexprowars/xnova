<template>
	<Head :title="$t('pages.messages.write.page_title')"/>
	<div class="page-messages-write">
		<MessageForm :id="page.id" :to="page.to" :message="page.message"/>
	</div>
</template>

<script setup>
	import MessageForm from '~/components/Page/Messages/Form.vue'
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	});
</script>