<template>
	<Head :title="$t('pages.sim.result')"/>
	<div class="game-page page-sim-report">
		<UiHeading :title="$t('pages.sim.result')" class="game-heading"/>
		<div class="page-battle-report" v-html="page.report"></div>
		<UiPanel class="game-panel">
			<div class="game-form">
				<label>
					{{ $t('pages.sim.result_link') }}
					<input type="text" :value="host + '/sim/report/' + page.uuid" readonly @focus="$event.target.select()">
				</label>
			</div>
		</UiPanel>
		<UiPanel :title="$t('pages.sim.losses_heading')" v-if="page.statistics" class="game-panel">
			<table class="game-table">
				<thead>
					<tr>
						<th>№</th>
						<th>{{ $t('pages.sim.attacker_losses') }}</th>
						<th>{{ $t('pages.sim.defender_losses') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(s, i) in page.statistics" :key="i">
						<td>{{ i + 1 }}</td>
						<td>{{ $formatNumber(s.att) }}</td>
						<td>{{ $formatNumber(s.def) }}</td>
					</tr>
				</tbody>
			</table>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiHeading, UiPanel } from '~/components/UI';
	import { Head } from '@inertiajs/vue3';
	import App from '~/App.vue';
	import EmptyLayout from '~/layouts/EmptyLayout.vue';
	import { computed } from 'vue';

	defineOptions({
		layout: [App, EmptyLayout],
	});

	defineProps({
		page: Object,
	})

	const host = computed(() => (import.meta.env.VITE_APP_URL || (typeof window !== 'undefined' ? window.location.origin : '')).replace(/\/$/, ''));
</script>