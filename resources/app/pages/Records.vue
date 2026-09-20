<template>
	<Head :title="$t('pages.records.page_title')"/>
	<div class="block page-records">
		<div class="title game-list-heading">
			<span>{{ $t('pages.records.heading') }}</span>
			<span class="game-list-meta">{{ $t('pages.records.updated_at', { time: $formatDate(page.update, 'DD MMM YYYY HH:mm:ss') }) }}</span>
		</div>
		<div class="table-responsive game-list-scroll">
			<table class="table game-list-table records-table">
				<tbody v-for="(list, group) in page.items" :key="group">
					<tr class="game-list-section">
						<th scope="col">{{ group }}</th>
						<th scope="col">{{ $t('pages.records.col_player') }}</th>
						<th scope="col" class="game-list-number">{{ $t('pages.records.col_level') }}</th>
					</tr>
					<tr v-for="(info, building) in list" :key="building">
						<th scope="row">{{ building }}</th>
						<td class="records-winner" :class="{ 'is-empty': info.winner === '-' }">{{ info.winner }}</td>
						<td class="game-list-number"><span class="records-value" :class="{ 'is-empty': info.count === '-' }">{{ info.count }}</span></td>
					</tr>
					<tr v-if="Object.keys(list).length === 0"><td colspan="3" class="game-list-empty">{{ $t('pages.records.empty_list') }}</td></tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	});
</script>