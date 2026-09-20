<template>
	<div v-if="items.length" class="block page-building-unit-queue">
		<div class="title">{{ $t('pages.building.unit_queue') }}</div>
		<div>
			<div
				v-for="(item, index) in items"
				:key="item.item + ':' + item.date"
				class="build-queue-row unit-queue-row"
				:class="{ 'is-current': index === 0 }"
			>
				<div class="build-queue-item">
					<img class="build-queue-image" :src="'/assets/images/elements/' + item.item + '.webp'" alt="" width="42" height="42">
					<div class="build-queue-description">
						<div class="build-queue-name">
							<strong>{{ $t('tech.' + item.item) }}</strong>
							<span class="unit-queue-count">× {{ $formatNumber(item.remainingCount) }}</span>
						</div>
						<div v-if="index === 0 && item.nextUnitTime !== null" class="unit-queue-next">
							{{ $t('pages.building.unit_queue_next') }}
							<span>{{ $formatTime(item.nextUnitTime) }}</span>
						</div>
					</div>
				</div>
				<div class="build-queue-time">
					<div class="build-queue-time-label">{{ $t('pages.building.unit_queue_batch') }}</div>
					<div class="build-queue-timer">{{ $formatTime(item.remainingTime) }}</div>
				</div>
			</div>
		</div>
		<div class="unit-queue-total">
			<span>{{ $t('pages.building.unit_queue_total') }}</span>
			<strong>{{ $formatTime(leftTime) }}</strong>
		</div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { useNow } from '@vueuse/core';
	import dayjs from 'dayjs';

	const props = defineProps({
		queue: {
			type: Array,
			default: () => []
		}
	});

	const now = useNow({ interval: 1000 });
	const items = computed(() => props.queue.flatMap((item) => {
		const remainingTime = Math.ceil(dayjs(item.date).diff(now.value) / 1000);

		if (remainingTime <= 0) {
			return [];
		}

		const remainingCount = item.time > 0
			? Math.min(item.count, Math.ceil(remainingTime / item.time))
			: item.count;

		return [{
			...item,
			remainingTime,
			remainingCount,
			nextUnitTime: item.time > 0 ? Math.min(item.time, remainingTime - (remainingCount - 1) * item.time) : null,
		}];
	}));
	const leftTime = computed(() => items.value.at(-1)?.remainingTime ?? 0);
</script>