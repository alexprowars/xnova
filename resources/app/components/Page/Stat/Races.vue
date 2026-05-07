<template>
	<div class="block page-stat-races">
		<div class="content">
			<div class="block-table text-center">
				<div class="grid grid-cols-12">
					<div class="c col-span-2">{{ $t('pages.stats.races_table_rank') }}</div>
					<div class="c col-span-1">&nbsp;</div>
					<div class="c col-span-3">{{ $t('pages.stats.races_table_players_count') }}</div>
					<div class="c col-span-3">{{ $t('pages.stats.races_table_points_total') }}</div>
					<div class="c col-span-3">{{ $t('pages.stats.races_table_points_per_player') }}</div>
				</div>
				<div v-for="item in items" class="page-stat-races-row grid grid-cols-12">
					<div class="th col-span-2 middle">{{ item['place'] }}</div>
					<div class="th col-span-1 middle">
						<img :src="'/images/skin/race' + item['race'] + '.gif'" alt="" width="30" height="30">
					</div>
					<div class="th col-span-3 middle">{{ $formatNumber(item['count']) }}</div>
					<div class="th col-span-3 middle">{{ $formatNumber(item['points']) }}</div>
					<div class="th col-span-3 middle">
						{{ $formatNumber(Math.floor(item['points'] / item['count'])) }}
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	defineProps({
		items: {
			type: Array,
			default: () => []
		}
	});
</script>