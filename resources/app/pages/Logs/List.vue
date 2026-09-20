<template>
	<Head :title="$t('pages.logs.index.page_title')"/>
	<div class="block page-logs">
		<div class="title game-list-heading">
			<span>{{ $t('pages.logs.index.title') }}</span>
			<Link href="/logs/create" class="button game-list-action">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
					<path d="M12 5v14M5 12h14"/>
				</svg>
				{{ $t('pages.logs.index.add_new') }}
			</Link>
		</div>
		<div v-if="page.items.length" class="table-responsive game-list-scroll">
			<table class="table game-list-table logs-table">
				<thead>
					<tr>
						<th scope="col" class="logs-number">{{ $t('pages.logs.index.number') }}</th>
						<th scope="col">{{ $t('pages.logs.index.name') }}</th>
						<th scope="col" class="logs-action-cell">{{ $t('pages.logs.index.link') }}</th>
						<th scope="col" class="logs-action-cell">{{ $t('pages.logs.index.management') }}</th>
					</tr>
				</thead>
				<tbody>
					<ItemRow v-for="row in page.items" :key="row.id" :item="row"/>
				</tbody>
			</table>
		</div>
		<div v-else class="game-list-empty">{{ $t('pages.logs.index.no_logs') }}</div>
	</div>
</template>

<script setup>
	import ItemRow from '~/components/Page/Logs/ItemRow.vue';
	import { Head, Link } from '@inertiajs/vue3';

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