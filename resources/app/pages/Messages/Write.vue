<template>
	<Head :title="$t('pages.messages.write.page_title')"/>
	<div class="page-messages-write">
		<MessageForm :id="id" :to="to" :message="message"/>
	</div>
</template>

<script setup>
	import MessageForm from '../../components/Page/Messages/Form.vue'
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		id: {
			type: Number,
			default: 0,
		},
		to: {
			type: String,
			default: '',
		},
		message: {
			type: String,
			default: '',
		}
	});
</script>