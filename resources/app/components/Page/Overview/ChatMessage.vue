<template>
	<div class="activity">
		<div class="date1" style="display: inline-block;padding-right:4px;">{{ $formatDate(item['date'], 'HH:mm') }}</div>
		<div style="display: inline;white-space:pre-wrap" v-html="message"></div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';

	const props = defineProps({
		item: Object,
	});

	const message = computed(() => {
		return '<span class="title"><span class="to">' + props['item']['user'] + '</span> написал' + (props['item']['toi'].length ? ' <span class="to">' + props['item']['toi'].join(', ') + '</span>' : '') + '</span>: ' + props['item']['text'];
	})
</script>