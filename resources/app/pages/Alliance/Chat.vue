<template>
	<Head :title="$t('pages.alliance.chat.meta_title')"/>
	<div class="page-alliance page-alliance-chat">
		<AllianceBack/>
		<header class="alliance-heading">
			<h1>{{ $t('pages.alliance.chat.meta_title') }}</h1>
			<button type="button" class="button is-secondary" @click="router.reload()">{{ $t('pages.alliance.chat.link_refresh') }}</button>
		</header>
		<section class="alliance-panel">
			<div v-if="!page.items.length" class="alliance-empty">{{ $t('pages.alliance.chat.empty_messages') }}</div>
			<article
				v-for="item in page.items"
				:key="item.id"
				class="alliance-chat-message"
				:class="{ 'is-selected': marked.includes(item.id), 'is-own': item.user_id === user.id }"
			>
				<header>
					<a :href="'/players/' + item.user_id" target="_blank" rel="noopener noreferrer">{{ item.user }}</a>
					<time>{{ $formatDate(item.time, 'DD MMM HH:mm:ss') }}</time>
					<button
						type="button"
						class="button is-secondary icon-button"
						:title="$t('pages.alliance.ui.quote')"
						:aria-label="$t('pages.alliance.ui.quote')"
						@click="quote(item)"
					>
						<QuoteIcon aria-hidden="true"/>
					</button>
					<input
						v-if="page.owner"
						type="checkbox"
						:value="item.id"
						v-model="marked"
						:aria-label="$t('pages.alliance.ui.select_message', [item.user])"
					>
				</header>
				<div class="alliance-chat-body">
					<TextViewer v-if="user.options.bb_parser" :text="item.message"/>
					<div v-else>{{ item.message }}</div>
				</div>
			</article>
			<div v-if="page.pagination.total > page.pagination.limit" class="alliance-chat-pagination">
				<Pagination :options="page.pagination"/>
			</div>
			<div v-if="page.owner && marked.length" class="alliance-chat-moderation">
				<span>{{ $t('pages.alliance.ui.selected') }}: {{ marked.length }}</span>
				<select v-model="deleteType" :aria-label="$t('pages.alliance.chat.button_delete')">
					<option value="marked">{{ $t('pages.alliance.chat.delete_option_marked') }}</option>
					<option value="unmarked">{{ $t('pages.alliance.chat.delete_option_unmarked') }}</option>
					<option value="all">{{ $t('pages.alliance.chat.delete_option_all') }}</option>
				</select>
				<button type="button" class="button is-danger" :disabled="deleteForm.processing" @click="remove">
					{{ $t('pages.alliance.chat.button_delete') }}
				</button>
			</div>
		</section>
		<div v-for="(error, key) in deleteForm.errors" :key="key" class="alliance-errors">{{ error }}</div>
		<ChatMessageForm v-model="form.message" :processing="form.processing" :errors="form.errors" @send="send"/>
	</div>
</template>

<script setup>
	import { useI18n } from 'vue-i18n';

	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import QuoteIcon from '~/images/icons/editor/quote.svg?component';
	import useState from '~/composables/useState.js';
	import ChatMessageForm from '~/components/Page/Alliance/ChatMessageForm.vue';
	import { computed, ref } from 'vue';
	import TextViewer from '~/components/TextViewer.vue';
	import Pagination from '~/components/Pagination.vue';
	import { Head, router, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';

	const { t } = useI18n();

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

	const form = useForm({
		message: '',
	});

	const deleteForm = useForm({ type: 'marked', id: [] });

	const deleteType = ref('marked');
	const marked = ref([]);

	const state = useState();
	const user = computed(() => state.user);

	function quote (messageItem) {
		let text = messageItem['message'] || '';
		text = text.replace(/<br>/gi, "\n");
		text = text.replace(/<br \/>/gi, "\n");

		form.message = form.message + '[quote author=' + messageItem['user'] + ']' + text + '[/quote]';
	}

	function send() {
		if (form.processing) return;

		form.post('/alliance/chat', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.ui.sent'));

				form.reset();
			}
		});
	}

	function remove() {
		deleteForm.type = deleteType.value;
		deleteForm.id = marked.value;
		deleteForm.delete('/alliance/chat', {
			onSuccess() {
				marked.value = [];
			}
		});
	}
</script>