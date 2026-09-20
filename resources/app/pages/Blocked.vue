<template>
	<Head :title="$t('pages.blocked.meta_title')"/>
	<div class="game-page page-blocked">
		<UiHeading :title="$t('pages.blocked.heading')" :count="page.items.length" class="game-heading"/>
		<UiPanel class="game-panel">
			<UiEmptyState v-if="!page.items.length">{{ $t('pages.blocked.empty_state') }}</UiEmptyState>
			<div v-else class="game-table-wrap">
				<table class="game-table blocked-table">
					<thead>
						<tr>
							<th>{{ $t('pages.blocked.col_player') }}</th>
							<th>{{ $t('pages.blocked.col_block_start') }}</th>
							<th>{{ $t('pages.blocked.col_block_end') }}</th>
							<th>{{ $t('pages.blocked.col_reason') }}</th>
							<th>{{ $t('pages.blocked.col_moderator') }}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in page.items" :key="index">
							<td :data-label="$t('pages.blocked.col_player')">
								<ModalLink navigate :href="'/players/' + item.user.id">{{ item.user.name }}</ModalLink>
							</td>
							<td :data-label="$t('pages.blocked.col_block_start')">
								<time>
									{{ $formatDate(item.date, 'DD MMM YYYY') }}
									<span>{{ $formatDate(item.date, 'HH:mm') }}</span>
								</time>
							</td>
							<td :data-label="$t('pages.blocked.col_block_end')">
								<time>
									{{ $formatDate(item.date_end, 'DD MMM YYYY') }}
									<span>{{ $formatDate(item.date_end, 'HH:mm:ss') }}</span>
								</time>
							</td>
							<td class="blocked-reason" :data-label="$t('pages.blocked.col_reason')">{{ item.reason }}</td>
							<td :data-label="$t('pages.blocked.col_moderator')">
								<ModalLink navigate :href="'/players/' + item.moderator.id">{{ item.moderator.name }}</ModalLink>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div v-if="page.items.length" class="game-panel-footer">
				{{ $t('pages.blocked.footer_total', { count: page.items.length }) }}
			</div>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiEmptyState, UiHeading, UiPanel } from '~/components/UI';
	import { ModalLink } from '@inertiaui/modal-vue';
	import { Head } from '@inertiajs/vue3';

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
</script>