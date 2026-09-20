<template>
	<Head :title="$t('pages.support.index.head_title')"/>
	<div class="page-support">
		<UiHeading :title="$t('pages.support.index.title')" :count="page.items.length" class="support-heading"><template #actions><UiButton v-if="!request" @click="newRequest"><PlusIcon aria-hidden="true"/>{{ $t('pages.support.index.create_request') }}</UiButton></template></UiHeading>
		<CreateTicket v-if="request" @close="request = false"/>
		<UiPanel clip>
			<UiEmptyState v-if="!page.items.length">{{ $t('pages.support.index.empty') }}</UiEmptyState>
			<div v-else class="support-table-wrap"><UiTable class="support-table"><thead><tr><th scope="col">{{ $t('pages.support.index.col_id') }}</th><th scope="col">{{ $t('pages.support.index.col_subject') }}</th><th scope="col">{{ $t('pages.support.index.col_status') }}</th><th scope="col">{{ $t('pages.support.index.col_date') }}</th></tr></thead><tbody><ListItem v-for="item in page.items" :key="item.id" :item="item"/></tbody></UiTable></div>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiEmptyState, UiHeading, UiPanel, UiTable } from '~/components/UI';
	import PlusIcon from '~/images/icons/plus.svg?component';
	import CreateTicket from '~/components/Page/Support/Create.vue';
	import { ref } from 'vue';
	import ListItem from '~/components/Page/Support/ListItem.vue';
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

	const request = ref(false);

	function newRequest () {
		request.value = !request.value;
	}
</script>