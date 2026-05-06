<template>
	<div v-if="messages.length > 0" class="block">
		<div class="title">{{ $t('menu.chat') }}</div>
		<div class="content">
			<div class="overflow-y-auto overflow-x-hidden max-h-72">
				<ChatMessage v-for="message in messages" :key="message.id" :item="message"/>
			</div>
		</div>
	</div>
</template>

<script setup>
	import ChatMessage from '../Overview/ChatMessage.vue';
	import { inject, onMounted } from 'vue';

	const chatStore = inject('chat');

	const { messages } = chatStore;

	onMounted(() => {
		chatStore.loadMessages();
	});
</script>