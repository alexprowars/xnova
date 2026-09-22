<template>
	<Head :title="$t('pages.chat.meta_title')"/>
	<section class="page-chat" :aria-label="$t('pages.chat.meta_title')">
		<div class="page-chat-header">
			<h1>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
					<path d="M5 4h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-6 3V6a2 2 0 0 1 2-2Z"/>
					<path d="M7 9h10M7 13h6"/>
				</svg>
				{{ $t('pages.chat.meta_title') }}
			</h1>
			<button type="button" class="page-chat-clear" @click="clear">
				<TrashIcon stroke-width="1.5" aria-hidden="true"/>
				{{ $t('pages.chat.button_clear') }}
			</button>
		</div>
		<div ref="chatboxRef" class="page-chat-messages" role="log" :aria-label="$t('menu.chat')">
			<ChatMessage v-for="item in messages" :key="item.id" :item="item" @player="toPlayer" @private="toPrivate"/>
			<div v-if="!messages.length" class="page-chat-empty">{{ $t('pages.chat.empty_state') }}</div>
		</div>
		<form class="page-chat-compose" @submit.prevent="sendMessage">
			<div class="page-chat-toolbar">
				<div class="page-chat-tools">
					<button
						type="button"
						:title="$t('pages.chat.toolbar_insert_link')"
						:aria-label="$t('pages.chat.toolbar_insert_link')"
						@click="addTag('[url]|[/url]', 1)"
					>
						<LinkIcon stroke-width="1.5" aria-hidden="true"/>
					</button>
					<button
						type="button"
						:title="$t('pages.chat.toolbar_insert_image')"
						:aria-label="$t('pages.chat.toolbar_insert_image')"
						@click="addTag('[img]|[/img]', 3)"
					>
						<ImageIcon stroke-width="1.5" aria-hidden="true"/>
					</button>
					<Popover>
						<button type="button" :title="$t('pages.chat.toolbar_smilies')" :aria-label="$t('pages.chat.toolbar_smilies')">
							<SmileIcon stroke-width="1.5" aria-hidden="true"/>
						</button>
						<template #content>
							<div class="page-chat-smiles">
								<button v-for="smile in smilesList" :key="smile" type="button" :aria-label="smile" @click="addSmile(smile)">
									<img :src="'/assets/images/smile/' + smile + '.gif'" :alt="smile" loading="lazy">
								</button>
							</div>
						</template>
					</Popover>
				</div>
				<span class="page-chat-counter">{{ message.length }} / 750</span>
			</div>
			<div class="page-chat-input-row">
				<input
					ref="textRef"
					class="page-chat-message"
					type="text"
					v-model="message"
					:placeholder="$t('pages.chat.message_placeholder')"
					:aria-label="$t('pages.chat.message_placeholder')"
					autocomplete="off"
					maxlength="750"
				>
				<button
					type="submit"
					class="page-chat-send button"
					:disabled="!message.trim()"
					:title="$t('pages.chat.button_send')"
					:aria-label="$t('pages.chat.button_send')"
				>
					<SendIcon stroke-width="1.5" aria-hidden="true"/>
					<span>{{ $t('pages.chat.button_send') }}</span>
				</button>
			</div>
		</form>
	</section>
</template>

<script setup>
	import TrashIcon from '~/images/icons/trash.svg?component';
	import LinkIcon from '~/images/icons/editor/link.svg?component';
	import ImageIcon from '~/images/icons/editor/image.svg?component';
	import SmileIcon from '~/images/icons/editor/smile.svg?component';
	import SendIcon from '~/images/icons/send.svg?component';
	import { useI18n } from 'vue-i18n';
	import { inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
	import parser from '~/utils/parser';
	import ChatMessage from '~/components/Page/Chat/ChatMessage.vue';
	import { Head } from '@inertiajs/vue3';
	import Popover from '~/components/Popover.vue';

	const { t } = useI18n();

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const chatStore = inject('chat');

	const chatboxRef = ref(null);
	const textRef = ref(null);
	const smilesList = ref(parser.patterns.smiles);
	const message = ref('');
	const { messages } = chatStore;

	onMounted(() => {
		chatStore.loadMessages();

		window.addEventListener('resize', scrollToBottom, true);
	});

	onBeforeUnmount(() => {
		window.removeEventListener('resize', scrollToBottom);
	});

	watch(message, () => {
		textRef.value.focus()
	});

	watch(messages, () => {
		setTimeout(scrollToBottom, 250);

		chatStore.clearUnread();
	});

	function scrollToBottom () {
		if (chatboxRef.value) {
			chatboxRef.value.scrollTop = chatboxRef.value.scrollHeight
		}
	}

	function addTag (tag, type) {
		let len = message.value.length;
		let start = textRef.value.selectionStart;
		let end = textRef.value.selectionEnd;

		let rep = parser.addTag(tag, message.value.substring(start, end), type)

		message.value = message.value.substring(0, start) + rep + message.value.substring(end, len)
	}

	function addSmile (smile){
		message.value = message.value + ' :'+smile+':';
	}

	function toPlayer (user) {
		message.value = t('chat_recipient.public') + ' [' + user + '] ' + message.value;
	}

	function toPrivate (user) {
		message.value = t('chat_recipient.private') + ' [' + user + '] ' + message.value;
	}

	function clear () {
		chatStore.clear();
	}

	function sendMessage () {
		if (!message.value.trim()) {
			return;
		}

		chatStore.sendMessage(message.value);
		message.value = '';
	}
</script>