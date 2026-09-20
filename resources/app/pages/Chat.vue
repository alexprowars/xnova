<template>
	<Head :title="$t('pages.chat.meta_title')"/>
	<section class="page-chat" :aria-label="$t('pages.chat.meta_title')">
		<div class="page-chat-header">
			<h1><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 4h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-6 3V6a2 2 0 0 1 2-2Z"/><path d="M7 9h10M7 13h6"/></svg>{{ $t('pages.chat.meta_title') }}</h1>
			<button type="button" class="page-chat-clear" @click="clear">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/></svg>
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
					<button type="button" :title="$t('pages.chat.toolbar_insert_link')" :aria-label="$t('pages.chat.toolbar_insert_link')" @click="addTag('[url]|[/url]', 1)">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="m10 13 4-4M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m2 1 1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0" transform="translate(1 0)"/></svg>
					</button>
					<button type="button" :title="$t('pages.chat.toolbar_insert_image')" :aria-label="$t('pages.chat.toolbar_insert_image')" @click="addTag('[img]|[/img]', 3)">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 5-5 4 4 4-6 5 7"/></svg>
					</button>
					<Popper :triggers="['click']" :popper-triggers="['click']">
						<button type="button" :title="$t('pages.chat.toolbar_smilies')" :aria-label="$t('pages.chat.toolbar_smilies')">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 14s1 3 4 3 4-3 4-3M8 8v1M16 8v1"/></svg>
						</button>
						<template #content>
							<div class="page-chat-smiles">
								<button v-for="smile in smilesList" :key="smile" type="button" :aria-label="smile" @click="addSmile(smile)"><img :src="'/assets/images/smile/' + smile + '.gif'" :alt="smile" loading="lazy"></button>
							</div>
						</template>
					</Popper>
				</div>
				<span class="page-chat-counter">{{ message.length }} / 750</span>
			</div>
			<div class="page-chat-input-row">
				<input ref="textRef" class="page-chat-message" type="text" v-model="message" :placeholder="$t('pages.chat.message_placeholder')" :aria-label="$t('pages.chat.message_placeholder')" autocomplete="off" maxlength="750">
				<button type="submit" class="page-chat-send" :disabled="!message.trim()" :title="$t('pages.chat.button_send')" :aria-label="$t('pages.chat.button_send')">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 3-7 18-4-7-7-4 18-7Z"/><path d="m10 14 6-6"/></svg>
					<span>{{ $t('pages.chat.button_send') }}</span>
				</button>
			</div>
		</form>
	</section>
</template>

<script setup>
	import { inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
	import parser from '~/utils/parser';
	import ChatMessage from '~/components/Page/Chat/ChatMessage.vue';
	import { Head } from '@inertiajs/vue3';
	import Popper from '~/components/Popper.vue';

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
		message.value = 'для [' + user + '] ' + message.value;
	}

	function toPrivate (user) {
		message.value = 'приватно [' + user + '] ' + message.value;
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