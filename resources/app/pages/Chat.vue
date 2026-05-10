<template>
	<Head :title="$t('pages.chat.meta_title')"/>
	<div class="block-table page-chat divide-y">
		<div class="th p-2">
			<div ref="chatboxRef" class="page-chat-messages">
				<ChatMessage v-for="(item, i) in messages" :key="i" :item="item" @player="toPlayer" @private="toPrivate"/>
			</div>
		</div>
		<div class="th text-center">
			<div class="flex items-center gap-2 mb-2">
				<div class="grow">
					<input ref="textRef" class="page-chat-message" type="text" v-model="message" @keypress.enter.prevent="sendMessage" maxlength="750">
				</div>
				<div>
					<div class="editor-component-toolbar inline-block p-0">
						<button type="button" class="buttons" :title="$t('pages.chat.toolbar_insert_link')" @click="addTag('[url]|[/url]', 1)">
							<span class="sprite bb_world_link"></span>
						</button>
						<button type="button" class="buttons" :title="$t('pages.chat.toolbar_insert_image')" @click="addTag('[img]|[/img]', 3)">
							<span class="sprite bb_picture_add"></span>
						</button>
						<Popper :triggers="['click']" :popper-triggers="['click']">
							<button type="button" class="buttons" :title="$t('pages.chat.toolbar_smilies')">
								<span class="sprite bb_emoticon_grin"></span>
							</button>
							<template #content>
								<div class="smiles">
									<img v-for="smile in smilesList" :src="'/assets/images/smile/'+smile+'.gif'" :alt="smile" @click="addSmile(smile)">
								</div>
							</template>
						</Popper>
					</div>
				</div>
			</div>

			<button class="button" @click.prevent="clear">{{ $t('pages.chat.button_clear') }}</button>
			<button class="button" @click.prevent="sendMessage">{{ $t('pages.chat.button_send') }}</button>
		</div>
	</div>
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
		chatStore.sendMessage(message.value);
		message.value = '';
	}
</script>
