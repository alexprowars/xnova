<template>
	<div v-if="queue.length" class="page-building-build-queue">
		<div class="build-queue-list">
			<BuildQueueRow v-for="(item, index) in queue" :key="item.id" :index="index" :item="item"/>
		</div>
	</div>
</template>

<script setup>
	import BuildQueueRow from './BuildQueueRow.vue';
	import { computed, onBeforeUnmount, watch } from 'vue';
	import { useUpdateInterval } from '~/composables/useUpdateInterval.js';
	import dayjs from 'dayjs';
	import { router } from '@inertiajs/vue3';

	const props = defineProps({
		queue: {
			type: Array,
			default: () => []
		},
	});

	const now = useUpdateInterval();
	const endTime = computed(() => {
		return props.queue.length ? dayjs(props.queue[0].date).diff(now.value) / 1000 : 0
	});

	let timeout;

	onBeforeUnmount(() => {
		clearTimeout(timeout);
	});

	watch(endTime, (val) => {
		if (val <= 0) {
			timeout = setTimeout(() => {
				router.reload();
			}, 5000);
		}
	});
</script>