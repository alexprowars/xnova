<template>
	<tr :class="{ 'is-marked': marked }">
		<td class="stats-place">
			<Rank :place="item.place" :diff="item.diff"/>
		</td>
		<th scope="row">
			<div class="stats-player-identity">
				<RaceIcon
					v-if="item.race"
					:code="item.race"
					:style="{ color: 'var(--faction-' + item.race + '-color)' }"
					width="18"
					height="18"
					:aria-label="$t('races.' + item.race)"
					role="img"
					focusable="false"
					:title="$t('races.' + item.race)"
				/>
				<ModalLink navigate :href="'/players/' + item.id" :class="{ 'stats-highlight': marked }">{{ item.name }}</ModalLink>
			</div>
			<Link
				v-if="item.alliance"
				class="stats-mobile-alliance"
				:class="{ 'stats-highlight': item.alliance.marked }"
				:href="'/alliance/info/' + item.alliance.id"
			>
				{{ item.alliance.name }}
			</Link>
		</th>
		<td class="stats-player-actions">
			<SendMessagePopup
				v-if="user"
				:title="$t('send_message')"
				:aria-label="$t('send_message')"
				:id="item.id"
				class="button is-secondary icon-button"
			>
				<MessageIcon stroke-width="1.5" aria-hidden="true"/>
			</SendMessagePopup>
		</td>
		<td class="stats-alliance-column">
			<Link v-if="item.alliance" :class="{ 'stats-highlight': item.alliance.marked }" :href="'/alliance/info/' + item.alliance.id">
				{{ item.alliance.name }}
			</Link>
			<span v-else class="stats-muted">—</span>
		</td>
		<td class="stats-points">
			<Link :href="'/players/' + item.id + '/stats'">{{ $formatNumber(item.points) }}</Link>
		</td>
	</tr>
</template>

<script setup>
	import MessageIcon from '~/images/icons/message.svg?component';
	import Rank from './Rank.vue';
	import useState from '~/composables/useState.js';
	import SendMessagePopup from '../Messages/SendMessagePopup.vue';
	import { Link } from '@inertiajs/vue3';
	import { computed } from 'vue';
	import { useUrlSearchParams } from '@vueuse/core';
	import { ModalLink } from '@inertiaui/modal-vue';
	import RaceIcon from '~/components/RaceIcon.vue';

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