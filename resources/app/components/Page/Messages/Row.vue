<template>
	<article class="message-card" :class="['message-type-' + item.type, { 'is-selected': deleteModel.includes(item.id) }]">
		<header class="message-header">
			<input v-if="canDelete" name="delete[]" type="checkbox" :value="item.id" v-model="deleteModel" :aria-label="$t('pages.messages.row.select')" :title="$t('pages.messages.row.select')">
			<div class="message-identity">
				<ModalLink v-if="item.from > 0" navigate :href="'/players/' + item.from" class="message-subject" :title="$t('pages.messages.row.from_title')" v-html="item.subject"/>
				<span v-else class="message-subject" v-html="item.subject"/>
				<div class="message-meta"><span class="message-category">{{ $t('message_types.' + item.type) }}</span><time :datetime="item.date">{{ $formatDate(item.date, 'DD MMM YYYY HH:mm:ss') }}</time></div>
			</div>
			<div v-if="item.type === 1" class="message-actions">
				<Link :href="'/messages/write/' + item.from" class="message-action" :aria-label="$t('pages.messages.row.reply_title')" :title="$t('pages.messages.row.reply_title')"><MessageIcon name="reply"/></Link>
				<Link :href="'/messages/write/' + item.from + '?quote=' + item.id" class="message-action" :aria-label="$t('pages.messages.row.quote_title')" :title="$t('pages.messages.row.quote_title')"><MessageIcon name="quote"/></Link>
				<button type="button" class="message-action message-report" @click="abuseAction" :aria-label="$t('pages.messages.row.abuse_title')" :title="$t('pages.messages.row.abuse_title')"><MessageIcon name="flag"/></button>
			</div>
		</header>
		<div class="message-body">
			<TextViewer v-if="user.options?.bb_parser" :text="item.message"/>
			<div v-else v-html="sanitizeHtml(item.message)"/>
		</div>
	</article>
</template>

<script setup>
	import MessageIcon from '~/components/Page/Messages/MessageIcon.vue';
	import useState from '~/composables/useState.js';
	import { Link, useForm } from '@inertiajs/vue3';
	import TextViewer from '~/components/TextViewer.vue';
	import { sanitizeHtml } from '~/utils/parser.js';
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { computed } from 'vue';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { ModalLink } from '@inertiaui/modal-vue';

	const { t } = useI18n();

	const props = defineProps({
		item: Object,
		canDelete: Boolean,
	});

	const state = useState();
	const user = computed(() => state.user);
	const deleteModel = defineModel('delete');

	function abuseAction () {
		openConfirmModal(
			null,
			t('pages.messages.row.abuse_confirm.title'),
			[{
				title: t('pages.messages.row.abuse_confirm.no'),
			}, {
				title: t('pages.messages.row.abuse_confirm.yes'),
				handler() {
					useForm().post('/messages/' + props.item['id'] + '/abuse', {
						onSuccess() {
							useSuccessNotification(t('pages.messages.row.abuse_confirm.success'));
						}
					});
				}
			}]
		);
	}
</script>