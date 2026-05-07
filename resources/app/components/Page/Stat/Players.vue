<template>
	<div class="block page-stat-players">
		<div class="content">
			<div class="block-table text-center middle">
				<div class="grid grid-cols-12">
					<div class="c sm:col-span-1 col-span-2 middle">{{ $t('pages.stats.players_table_rank') }}</div>
					<div class="c sm:col-span-1 hidden sm:block middle">{{ $t('pages.stats.players_table_delta') }}</div>
					<div class="c sm:col-span-4 col-span-5 middle">{{ $t('pages.stats.players_table_player') }}</div>
					<div class="c sm:col-span-1 col-span-2 middle">&nbsp;</div>
					<div class="c sm:col-span-3 hidden sm:block middle">{{ $t('pages.stats.players_table_alliance') }}</div>
					<div class="c sm:col-span-2 col-span-3 middle">{{ $t('pages.stats.players_table_points') }}</div>
				</div>
				<PlayersRow v-for="item in items" :key="item['id']" :item="item"/>
			</div>
		</div>
	</div>
</template>

<script setup>
	import PlayersRow from './PlayersRow.vue';

	defineProps({
		items: Array,
		default: () => []
	});
</script>
