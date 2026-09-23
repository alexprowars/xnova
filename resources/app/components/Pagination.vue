<template>
	<PaginationRoot
		:page="options.page"
		:total="options.total"
		:items-per-page="options.limit"
		:sibling-count="3"
		show-edges
		aria-label="Пагинация"
		@update:page="load"
	>
		<PaginationList v-slot="{ items }" as="ul" class="pagination">
			<li v-for="(item, index) in items" :key="item.type === 'page' ? item.value : 'ellipsis-' + index" :class="{ active: options.page === item.value }">
				<PaginationListItem v-if="item.type === 'page'" :value="item.value" :aria-label="'Страница ' + item.value">
					{{ item.value }}
				</PaginationListItem>
				<PaginationEllipsis v-else as="span" aria-hidden="true">...</PaginationEllipsis>
			</li>
		</PaginationList>
	</PaginationRoot>
</template>

<script setup>
	import { router, usePage } from '@inertiajs/vue3';
	import { PaginationEllipsis, PaginationList, PaginationListItem, PaginationRoot } from 'reka-ui';

	defineProps({
		options: {
			type: Object,
			required: true,
		}
	});

	const inertiaPage = usePage();

	function load (page) {
		const params = new URLSearchParams(window.location.search);
		params.set('page', page);

		router.get(inertiaPage.url, Object.fromEntries(params));
	}
</script>