import { computed, ref } from 'vue'
import { useHttp } from '@inertiajs/vue3';
import parser from '~/utils/parser.js';

const patterns = {
	find: [
		/script/g,
		/\[b](.*?)\[\/b]/gi,
		/\[i](.*?)\[\/i]/gi,
		/\[u](.*?)\[\/u]/gi,
		/\[s](.*?)\[\/s]/gi,
		/\[left](.*?)\[\/left]/gi,
		/\[center](.*?)\[\/center]/gi,
		/\[right](.*?)\[\/right]/gi,
		/\[justify](.*?)\[\/justify]/gi,
		/\[size=([1-9]|1[0-9]|2[0-5])](.*?)\[\/size]/gi,
		/\[img](https?:\/\/.*?\.(?:jpg|jpeg|png))\[\/img]/gi,
		/\[url=((?:ftp|https?):\/\/.*?)](.*?)\[\/url]/g,
		/\[url]((?:ftp|https?):\/\/.*?)\[\/url]/g,
		/\[p](.*?)\[\/p]/gi,
		/\[([1-9]):([0-9]{1,3}):([0-9]{1,2})]/gi
	],
	replace: [
		'',
		'<strong>$1</strong>',
		'<em>$1</em>',
		'<span style="text-decoration: underline;">$1</span>',
		'<span style="text-decoration: line-through;">$1</span>',
		'<div class="text-left">$1<\/div>',
		'<div class="text-center">$1<\/div>',
		'<div class="text-right">$1<\/div>',
		'<div style="text-align:justify;">$1<\/div>',
		'<span style="font-size: $1px;">$2</span>',
		'<a href="$1" target="_blank"><img src="$1" style="max-width:350px;" alt=""></a>',
		'<a href="$1" target="_blank">$2</a>',
		'<a href="$1" target="_blank">$1</a>',
		'<p>$1</p>',
		'<a href="/galaxy/?galaxy=$1&system=$2">[$1:$2:$3]</a>'
	]
}

export function reformatMessage(message) {
	patterns.find.forEach((item, i) => {
		message = message.replace(item, patterns.replace[i])
	})

	let j = 0;

	parser.patterns.smiles.every((smile) => {
		while (message.indexOf(':' + smile + ':') >= 0) {
			message = message.replace(':' + smile + ':', '<img src="/assets/images/smile/' + smile + '.gif" alt="' + smile + '">')

			if (++j >= 3) {
				break;
			}
		}

		return j < 3;
	})

	return message;
}

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

		await useHttp({ message }).post('/chat');
	}

	async function loadMessages () {
		if (messages.value.length) {
			return;
		}

		try {
			messages.value = await useHttp().get('/chat/last');
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
		message = { ...message, text: reformatMessage(message['text']) };

		messages.value.push(message);
		unread.value += 1;
	}

	function setMessages (messages) {
		messages.value = messages.map((message) => ({ ...message, text: reformatMessage(message['text']) }));
	}

	function clearUnread () {
		unread.value = 0;
	}

	function incrementUnread () {
		unread.value += 1;
	}

	return { messages, unread, sortedMessages, sendMessage, loadMessages, clear, addMessage, setMessages, clearUnread, incrementUnread };
}