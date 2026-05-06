<template>
	<div v-if="queue.length" class="page-building-build-queue">
		<div class="block-table">
			<BuildQueueRow v-for="(item, index) in queue" :key="index" :index="index" :item="item"/>
		</div>
	</div>
</template>

<script setup>
	import BuildQueueRow from './BuildQueueRow.vue';
	import { computed, onBeforeUnmount, watch } from 'vue';
	import { useNow } from '@vueuse/core';
	import dayjs from 'dayjs';
	import { router } from '@inertiajs/vue3';

	const props = defineProps({
		queue: {
			type: Array,
			default: () => []
		},
	});

	const now = useNow({ interval: 1000 });
	const endTime = computed(() =>
		props.queue.length ? dayjs(props.queue[0].time).diff(now.value) / 1000 : 0
	);

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