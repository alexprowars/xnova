<template>
	<div class="stats-table-wrap">
		<table class="stats-table stats-races-table">
			<thead>
				<tr>
					<th scope="col" class="stats-place">{{ $t('pages.stats.races_table_rank') }}</th>
					<th scope="col">{{ $t('pages.stats.faction') }}</th>
					<th scope="col" class="stats-members">{{ $t('pages.stats.races_table_players_count') }}</th>
					<th scope="col" class="stats-points">
						{{ $t('pages.stats.races_table_points_total') }}
						<span class="stats-mobile-average">{{ $t('pages.stats.per_player') }}</span>
					</th>
					<th scope="col" class="stats-average">{{ $t('pages.stats.races_table_points_per_player') }}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="item in items" :key="item.race">
					<td class="stats-place">
						<Rank :place="item.place"/>
					</td>
					<th scope="row">
						<div class="stats-race-name">
							<RaceIcon v-if="item.race" :code="item.race" :style="{ color: 'var(--faction-' + item.race + '-color)' }" width="30" height="30" aria-hidden="true" focusable="false"/>
							<span>{{ $t('races.' + item.race) }}</span>
						</div>
					</th>
					<td class="stats-members">{{ $formatNumber(item.count) }}</td>
					<td class="stats-points">
						{{ $formatNumber(item.points) }}
						<span class="stats-mobile-average">{{ $formatNumber(item.count ? Math.floor(item.points / item.count) : 0) }}</span>
					</td>
					<td class="stats-average">{{ $formatNumber(item.count ? Math.floor(item.points / item.count) : 0) }}</td>
				</tr>
				<tr v-if="!items.length">
					<td colspan="5" class="stats-empty">{{ $t('pages.stats.empty') }}</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
	import Rank from './Rank.vue';
	import RaceIcon from '~/components/RaceIcon.vue';

	defineProps({
		items: {
			type: Array,
			default: () => []
		}
	});
</script>