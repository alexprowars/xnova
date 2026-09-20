<template>
	<div class="overview-queue-row">
		<img class="overview-queue-image" :src="'/assets/images/elements/' + item.item + '.webp'" alt="" width="38" height="38">
		<div class="overview-queue-description">
			<div class="overview-queue-name">
				<strong>{{ $t('tech.' + item.item) }}</strong>
				<span v-if="item.level" class="overview-queue-amount">{{ $t('pages.building.queue_level', { level: item.level }) }}</span>
				<span v-if="item.count" class="overview-queue-amount">× {{ $formatNumber(item.count) }}</span>
			</div>
			<button
				v-if="item.planet_id !== planet.id"
				type="button"
				class="overview-queue-planet is-link"
				@click="changePlanet(item.planet_id)"
			>
				{{ planetItem?.name }}
				<span aria-hidden="true">↗</span>
			</button>
			<span v-else class="overview-queue-planet">{{ planetItem?.name }}</span>
		</div>
		<div class="overview-queue-time">
			<div class="overview-queue-countdown">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
					<circle cx="12" cy="12" r="9"/>
					<path d="M12 7v5l3 2"/>
				</svg>
				<Timer :value="item.date"/>
			</div>
			<div class="overview-queue-date" :title="$t('pages.building.queue_completion')">
				{{ $formatDate(item.date, 'DD MMM HH:mm:ss') }}
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import Timer from '~/components/Timer.vue';
	import { changePlanet } from '~/utils/helpers.js';

	const { item } = defineProps({
		item: Object
	});

	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);

	const planetItem = computed(() => {
		return user.value['planets'].find((p) => p['id'] === item['planet_id']);
	});
</script>