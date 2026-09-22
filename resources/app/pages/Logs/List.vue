<template>
	<Head :title="$t('pages.logs.index.page_title')"/>
	<div class="block page-logs">
		<div class="title game-list-heading">
			<span>{{ $t('pages.logs.index.title') }}</span>
			<Link href="/logs/create" class="button game-list-action">
				<PlusIcon stroke-width="1.5" aria-hidden="true"/>
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
	import PlusIcon from '~/images/icons/plus.svg?component';
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