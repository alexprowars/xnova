<template>
	<Head title="Симуляция"/>
	<div class="combar-report text-center">
		<div v-html="report"></div>
		Ссылка на результат симуляции
		<div class="my-4 text-center">
			<input type="text" :value="host + '/sim/report/' + uuid" class="w-125 p-4">
		</div>
		<div v-if="statistics" class="my-4">
			<div class="mb-2">Результаты потерь после 50 симуляций:</div>
			<div class="block-table text-center w-max mx-auto">
				<div class="grid grid-cols-12">
					<div class="col-span-4 th">№</div>
					<div class="col-span-4 th">Потери атакующего</div>
					<div class="col-span-4 th">Потери защитника</div>
				</div>
				<div v-for="(s, i) in statistics" class="grid grid-cols-12">
					<div class="col-span-4 th">{{ (i + 1) }}</div>
					<div class="col-span-4 th">{{ $formatNumber(s['att']) }}</div>
					<div class="col-span-4 th">{{ $formatNumber(s['def']) }}</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import App from '~/App.vue';
	import EmptyLayout from '~/layouts/EmptyLayout.vue';
	import { computed } from 'vue';

	defineOptions({
		layout: [App, EmptyLayout],
	});

	defineProps({
		uuid: String,
		report: String,
		statistics: {
			type: Array,
			default: () => [],
		},
	})

	const host = computed(() => import.meta.env.VITE_APP_URL || '');
</script>