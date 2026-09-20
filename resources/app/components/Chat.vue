<template>
	<aside v-if="!mobile" class="component-chat" :class="{ active }" :aria-label="$t('menu.chat')">
		<button type="button" class="mini-chat-toggle" :aria-expanded="active" aria-controls="mini-chat-content" @click="toggleActive">
			<svg class="mini-chat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
				<path d="M5 4h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-6 3V6a2 2 0 0 1 2-2Z"/>
				<path d="M7 9h10M7 13h6"/>
			</svg>
			<span>{{ $t('menu.chat') }}</span>
			<span v-if="unread > 0" class="mini-chat-unread">{{ unread }}</span>
			<svg class="mini-chat-chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
				<path d="m5 12 5-5 5 5"/>
			</svg>
		</button>
		<div v-show="active" id="mini-chat-content" class="mini-chat-content">
			<div ref="chatRef" class="page-chat-messages" role="log" :aria-label="$t('menu.chat')">
				<ChatMessage v-for="item in sortedMessages" :key="item.id" :item="item" @player="toPlayer" @private="toPrivate"/>
				<div v-if="!sortedMessages.length" class="mini-chat-empty">{{ $t('pages.chat.empty_state') }}</div>
			</div>
			<form class="mini-chat-compose" @submit.prevent="sendMessage">
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
					class="mini-chat-send button icon-button"
					:disabled="!message.trim()"
					:title="$t('pages.chat.button_send')"
					:aria-label="$t('pages.chat.button_send')"
				>
					<svg
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						stroke-width="1.5"
						stroke-linecap="round"
						stroke-linejoin="round"
						aria-hidden="true"
					>
						<path d="m21 3-7 18-4-7-7-4 18-7Z"/>
						<path d="m10 14 6-6"/>
					</svg>
				</button>
			</form>
		</div>
	</aside>
</template>

<script setup>
	import { onBeforeUnmount, onMounted, ref, watch, inject } from 'vue';
	import ChatMessage from './Page/Chat/ChatMessage.vue';
	import { isMobile } from '~/utils/helpers.js';

	const props = defineProps({
		visible: {
			type: Boolean,
			default: false,
		}
	});

	const chatStore = inject('chat');

	const mobile = ref(isMobile() || !props.visible);
	const active = ref(localStorage?.getItem('mini-chat-active') === 'Y');
	const message = ref('');

	const textRef = ref(null);
	const chatRef = ref(null);

	const { unread, sortedMessages } = chatStore;

	onMounted(() => {
		if (active.value && !mobile.value) {
			chatStore.loadMessages();
		}

		window.addEventListener('resize', onResize, true);
	});

	onBeforeUnmount(() => {
		window.removeEventListener('resize', onResize);
	});

	watch(sortedMessages, () => {
		setTimeout(scrollToBottom, 250);

		if (active.value) {
			chatStore.clearUnread();
		}
	});

	watch(message, () => {
		textRef.value?.focus();
	});

	watch(() => props.visible, (value) => {
		mobile.value = isMobile() || !value;
	});

	function scrollToBottom () {
		if (chatRef.value) {
			chatRef.value.scrollTop = chatRef.value.scrollHeight;
		}
	}

	function toggleActive () {
		active.value = !active.value;

		try {
			localStorage.setItem('mini-chat-active', active.value ? 'Y' : 'N')
		} catch (e) {}

		if (active.value) {
			chatStore.loadMessages();
			chatStore.clearUnread();
			scrollToBottom();
		}
	}

	function toPlayer (user) {
		message.value = 'для [' + user + '] ' + message.value;
	}

	function toPrivate (user) {
		message.value = 'приватно [' + user + '] ' + message.value;
	}

	function sendMessage () {
		if (!message.value.trim()) {
			return;
		}

		chatStore.sendMessage(message.value);
		message.value = '';
	}

	function onResize () {
		if (active.value) {
			scrollToBottom()
		}
	}
</script>