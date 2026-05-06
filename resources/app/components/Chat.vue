<template>
	<div v-if="!mobile" class="component-chat" :class="{active: active}">
		<div class="block">
			<div class="title" @click="toggleActive">
				{{ $t('menu.chat') }}
				<span v-if="unread > 0">({{ unread }})</span>
			</div>
			<div v-show="active" class="content">
				<div class="th">
					<div ref="chatRef" class="page-chat-messages">
						<ChatMessage v-for="(item, i) in sortedMessages" :key="i" :item="item" @player="toPlayer" @private="toPrivate"/>
					</div>
				</div>
				<div class="th flex gap-2">
					<input ref="textRef" class="page-chat-message" type="text" v-model="message" @keydown.enter.prevent="sendMessage" maxlength="750">
					<button @click.prevent="sendMessage">Отправить</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { onBeforeUnmount, onMounted, ref, watch, inject } from 'vue';
	import ChatMessage from './Page/Chat/ChatMessage.vue';
	import { isMobile } from '../utils/helpers.js';

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
		chatStore.sendMessage(message.value);
		message.value = '';
	}

	function onResize () {
		if (active.value) {
			scrollToBottom()
		}
	}
</script>