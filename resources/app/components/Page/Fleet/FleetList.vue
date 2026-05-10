<template>
	<div class="block-table">
		<div v-if="fleets.length > 0" class="grid grid-cols-12 divide-x text-center">
			<div class="col-span-3 sm:col-span-1 th">{{ $t('pages.fleets.list.number') }}</div>
			<div class="col-span-6 sm:col-span-2 th">{{ $t('pages.fleets.list.mission') }}</div>
			<div class="col-span-3 sm:col-span-1 th">{{ $t('pages.fleets.list.quantity') }}</div>
			<div class="col-span-4 sm:col-span-3 th hidden sm:block">{{ $t('pages.fleets.list.target') }}</div>
			<div class="col-span-2 sm:col-span-3 th hidden sm:block">{{ $t('pages.fleets.list.return') }}</div>
			<div class="col-span-2 th hidden sm:block">{{ $t('pages.fleets.list.orders') }}</div>
		</div>

		<FleetRow v-for="(item, index) in fleets" :key="index" :i="index" :item="item"/>

		<div class="grid" v-if="fleets.length === 0">
			<div class="th text-center">{{ $t('pages.fleets.list.no_activity') }}</div>
		</div>

		<div class="grid" v-if="fleets.length >= user['fleets_max']">
			<div class="th negative text-center">{{ $t('pages.fleets.list.empty_slots') }}</div>
		</div>
	</div>
</template>

<script setup>
	import FleetRow from './FleetRow.vue';
	import { usePage } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineProps({
		fleets: Array,
	});

	const page = usePage();
	const user = computed(() => page.props.user);
</script>