<template>
	<tr class="page-stat-players-row" :class="{ 'is-marked': marked }">
		<td class="stats-place"><Rank :place="item.place" :diff="item.diff"/></td>
		<th scope="row" class="stats-player-name">
			<div class="stats-player-identity"><img v-if="item.race" :src="'/assets/images/skin/race' + item.race + '.gif'" width="18" height="18" :alt="$t('races.' + item.race)" :title="$t('races.' + item.race)"><ModalLink navigate :href="'/players/' + item.id" :class="{ 'stats-highlight': marked }">{{ item.name }}</ModalLink></div>
			<Link v-if="item.alliance" class="stats-mobile-alliance" :class="{ 'stats-highlight': item.alliance.marked }" :href="'/alliance/info/' + item.alliance.id">{{ item.alliance.name }}</Link>
		</th>
		<td class="stats-player-actions"><SendMessagePopup v-if="user" :title="$t('send_message')" :aria-label="$t('send_message')" :id="item.id" class="stats-message"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg></SendMessagePopup></td>
		<td class="stats-alliance-column"><Link v-if="item.alliance" :class="{ 'stats-highlight': item.alliance.marked }" :href="'/alliance/info/' + item.alliance.id">{{ item.alliance.name }}</Link><span v-else class="stats-muted">—</span></td>
		<td class="stats-points"><Link :href="'/players/' + item.id + '/stats'">{{ $formatNumber(item.points) }}</Link></td>
	</tr>
</template>

<script setup>
	import Rank from './Rank.vue';
	import useState from '~/composables/useState.js';
	import SendMessagePopup from '../Messages/SendMessagePopup.vue';
	import { Link } from '@inertiajs/vue3';
	import { computed } from 'vue';
	import { useUrlSearchParams } from '@vueuse/core';
	import { ModalLink } from '@inertiaui/modal-vue';

	const props = defineProps({
		item: Object,
	});

	const state = useState();
	const user = computed(() => state.user);
	const query = useUrlSearchParams('history');

	const marked = computed(() => {
		let id = parseInt(query.id || 0);

		return (!id && user.value?.['id'] === props.item['id']) || id === props.item['id'];
	});
</script>