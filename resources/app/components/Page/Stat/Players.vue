<template>
	<div class="stats-table-wrap">
		<table class="stats-table">
			<thead>
				<tr>
					<th scope="col" class="stats-place">{{ $t('pages.stats.players_table_rank') }}</th>
					<th scope="col">{{ $t('pages.stats.players_table_player') }}</th>
					<th scope="col" class="stats-player-actions">
						<span class="sr-only">{{ $t('send_message') }}</span>
					</th>
					<th scope="col" class="stats-alliance-column">{{ $t('pages.stats.players_table_alliance') }}</th>
					<th scope="col" class="stats-points">{{ $t('pages.stats.players_table_points') }}</th>
				</tr>
			</thead>
			<tbody>
				<PlayersRow v-for="item in items" :key="item.id" :item="item"/>
				<tr v-if="!items.length">
					<td colspan="5" class="stats-empty">{{ $t('pages.stats.empty') }}</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
	import PlayersRow from './PlayersRow.vue';

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	});
</script>