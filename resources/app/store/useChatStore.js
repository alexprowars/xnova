import { computed, ref } from 'vue'
import { reformat } from '../utils/chat.js';
import { useApiGet, useApiPost } from '../composables/useApi.js';

export default function useChatStore () {
	const messages = ref([]);
	const unread = ref(0);

	const sortedMessages = computed(() => {
		return messages.value.sort((a, b) => a['time'] < b['time'] ? -1 : 1);
	});

	async function sendMessage (message) {
		while (message.indexOf('\'') >= 0) {
			message = message.replace('\'', '`');
		}

		await useApiPost('/chat', { message });
	}

	async function loadMessages () {
		if (messages.value.length) {
			return;
		}

		try {
			messages.value = await useApiGet('/chat/last');
		} catch (error) {
			console.error(error);
		}

		clearUnread();
	}

	function clear () {
		setMessages([]);
		clearUnread();
	}

	function addMessage (message) {
		messages.value.push(reformat(message));
		unread.value += 1;
	}

	function setMessages (messages) {
		messages.value = messages.map((message) => reformat(message));
	}

	function clearUnread () {
		unread.value = 0;
	}

	function incrementUnread () {
		unread.value += 1;
	}

	return { messages, unread, sortedMessages, sendMessage, loadMessages, clear, addMessage, setMessages, clearUnread, incrementUnread };
}