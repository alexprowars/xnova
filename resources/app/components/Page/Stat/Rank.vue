<template>
	<div class="stats-position">
		<span class="stats-rank" :class="{ 'is-first': place === 1, 'is-second': place === 2, 'is-third': place === 3 }">{{ place }}</span>
		<span v-if="diff !== null" class="stats-delta" :class="{ 'is-up': diff > 0, 'is-down': diff < 0 }" :title="$t('pages.stats.position_change')"><span v-if="diff > 0">+{{ diff }}</span><span v-else-if="diff < 0">{{ diff }}</span><span v-else>—</span></span>
	</div>
</template>

<script setup>
	defineProps({
		place: Number,
		diff: { type: Number, default: null },
	});
</script>