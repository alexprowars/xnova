<template>
	<Modal>
		<Head :title="$t('pages.messages.write.page_title')"/>
		<div class="page-messages-write">
			<MessageForm :id="data.id" :to="data.to" :message="data.message"/>
		</div>
	</Modal>
</template>

<script setup>
	import MessageForm from '~/components/Page/Messages/Form.vue'
	import { Head } from '@inertiajs/vue3';
	import { Modal } from '@inertiaui/modal-vue';

	const props = defineProps({
		data: {
			type: Object,
		}
	});
</script>