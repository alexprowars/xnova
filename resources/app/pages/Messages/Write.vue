<template>
	<Head :title="$t('pages.messages.write.page_title')"/>
	<div class="page-messages-write">
		<MessageForm :id="data.id" :to="data.to" :message="data.message"/>
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

	const props = defineProps({
		data: {
			type: Object,
		}
	});
</script>