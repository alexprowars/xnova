<template>
	<div class="stats-table-wrap">
		<table class="stats-table">
			<thead>
				<tr>
					<th scope="col" class="stats-place">{{ $t('pages.stats.alliances_table_rank') }}</th>
					<th scope="col">{{ $t('pages.stats.alliances_table_name') }}</th>
					<th scope="col" class="stats-members">{{ $t('pages.stats.alliances_table_members') }}</th>
					<th scope="col" class="stats-points">
						{{ $t('pages.stats.alliances_table_points') }}
						<span class="stats-mobile-average">{{ $t('pages.stats.per_player') }}</span>
					</th>
					<th scope="col" class="stats-average">{{ $t('pages.stats.alliances_table_points_per_member') }}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="item in items" :key="item.id" :class="{ 'is-marked': item.name_marked }">
					<td class="stats-place">
						<Rank :place="item.place" :diff="item.diff"/>
					</td>
					<th scope="row">
						<Link :class="{ 'stats-highlight': item.name_marked }" :href="'/alliance/info/' + item.id">{{ item.name }}</Link>
					</th>
					<td class="stats-members">{{ item.members }}</td>
					<td class="stats-points">
						<Link :href="'/alliance/stat/' + item.id">{{ $formatNumber(item.points) }}</Link>
						<span class="stats-mobile-average">
							{{ $formatNumber(item.members ? Math.floor(item.points / item.members) : 0) }}
						</span>
					</td>
					<td class="stats-average">{{ $formatNumber(item.members ? Math.floor(item.points / item.members) : 0) }}</td>
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
	import { Link } from '@inertiajs/vue3';

	defineProps({
		items: Array
	});
</script>