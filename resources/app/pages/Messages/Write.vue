<template>
	<Head :title="$t('pages.messages.write.page_title')"/>
	<div class="page-messages-write">
		<Link href="/messages" class="message-back"><MessageIcon name="back"/>{{ $t('pages.messages.form.back') }}</Link>
		<MessageForm :id="page.id" :to="page.to" :message="page.message"/>
	</div>
</template>

<script setup>
	import MessageForm from '~/components/Page/Messages/Form.vue'
	import MessageIcon from '~/components/Page/Messages/MessageIcon.vue';
	import { Head, Link } from '@inertiajs/vue3';

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