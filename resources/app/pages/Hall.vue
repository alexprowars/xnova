<template>
	<Head :title="$t('pages.hall.head_title')"/>
	<div class="block page-hall">
		<div class="title game-list-heading">
			<div class="hall-heading-title">
				<span class="hall-top">{{ $t('pages.hall.top') }}</span>
				{{ $t('pages.hall.block_heading') }}
			</div>
			<select v-model="type" :aria-label="$t('pages.hall.battle_type')">
				<option value="single">{{ $t('pages.hall.type_single') }}</option>
				<option value="team">{{ $t('pages.hall.type_team') }}</option>
			</select>
		</div>
		<div v-if="page.items.length" class="table-responsive game-list-scroll">
			<table class="table game-list-table hall-table">
				<thead>
					<tr>
						<th scope="col" class="hall-place">{{ $t('pages.hall.col_place') }}</th>
						<th scope="col">{{ $t(page.type === 'single' ? 'pages.hall.subtitle_single' : 'pages.hall.subtitle_team') }}</th>
						<th scope="col">{{ $t('pages.hall.col_outcome') }}</th>
						<th scope="col" class="hall-date">{{ $t('pages.hall.col_date') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(item, i) in page.items" :key="item.id" :class="{ 'hall-latest': page.last === item.id }">
						<td class="hall-place">
							<span class="hall-rank" :class="{ 'is-first': i === 0, 'is-second': i === 1, 'is-third': i === 2 }">
								{{ i + 1 }}
							</span>
						</td>
						<td class="hall-battle">
							<a v-if="item.report_id" :href="'/logs/' + item.report_id" target="_blank" rel="noopener">{{ item.title }} <span class="game-list-external" aria-hidden="true">↗</span></a>
							<span v-else>{{ item.title }}</span>
						</td>
						<td>
							<span
								class="hall-outcome"
								:class="{ 'is-draw': item.won === 0, 'is-attack': item.won === 1, 'is-defense': item.won !== 0 && item.won !== 1 }"
							>
								{{ $t(item.won === 0 ? 'pages.hall.draw' : item.won === 1 ? 'pages.hall.attacker_win' : 'pages.hall.defender_win') }}
							</span>
						</td>
						<td class="hall-date">
							<span>{{ $formatDate(item.date, 'DD MMM YYYY') }}</span>
							<small>{{ $formatDate(item.date, 'HH:mm:ss') }}</small>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div v-else class="game-list-empty">{{ $t('pages.hall.empty_list') }}</div>
	</div>
</template>

<script setup>
	import { ref, watch } from 'vue';
	import { Head, router } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	});

	const type = ref(props.page['type']);

	watch(type, (value) => {
		router.get('/hall', { type: value });
	});
</script>