<template>
	<UiEmptyState v-if="!items.length">{{ $t('pages.search.no_results') }}</UiEmptyState>
	<div v-else class="game-table-wrap">
		<table class="game-table">
			<thead>
				<tr>
					<th>{{ $t('pages.search.column_abbreviation') }}</th>
					<th>{{ $t('pages.search.column_name') }}</th>
					<th>{{ $t('pages.search.column_members') }}</th>
					<th>{{ $t('pages.search.column_points') }}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="item in items" :key="item.id">
					<td>
						<Link :href="'/alliance/info/' + item.id" class="search-alliance-tag">[{{ item.tag }}]</Link>
					</td>
					<td>
						<Link :href="'/alliance/info/' + item.id">{{ item.name }}</Link>
					</td>
					<td>{{ item.members }}</td>
					<td>{{ item.total_points }}</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
	import { UiEmptyState } from '~/components/UI';
	import { Link } from '@inertiajs/vue3';

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	})
</script>