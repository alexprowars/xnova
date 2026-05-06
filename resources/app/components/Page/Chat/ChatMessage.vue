<template>
	<div class="page-chat-messages-row text-left">
		<span :class="{date1: !item['me'] && !item['my'], date2: !!item['me'], date3: !!item['my']}" @click="emit('private', item['user'])">{{ $formatDate(item['date'], 'HH:mm') }}</span>
		<span v-if="item['my']" class="negative">{{ item['user'] }}</span><span v-else class="to" @click="emit('player', item['user'])">{{ item['user'] }}</span>:
		<span v-if="item['tou'].length" :class="[item['private'] ? 'private' : 'player']">
			{{ item['private'] ? 'приватно' : 'для' }} [<span v-for="(u, i) in item['tou']">{{ i > 0 ? ',' : '' }}<a v-if="!item['private']" @click.prevent="emit('player', u)">{{ u }}</a><a v-else @click.prevent="emit('private', u)">{{ u }}</a></span>]
		</span>
		<span class="page-chat-row-message" v-html="item['text']"></span>
	</div>
</template>

<script setup>
	defineProps({
		item: Object,
	});

	const emit = defineEmits(['player', 'private']);
</script>