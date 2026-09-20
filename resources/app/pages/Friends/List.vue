<template>
	<Head :title="$t('pages.friends.list.title')"/>
	<div class="page-friends">
		<UiHeading :title="$t('pages.friends.list.title')" :count="page.items.length"/>
		<FriendsNavigation active="list"/>
		<UiPanel clip><UiEmptyState v-if="!page.items.length">{{ $t('pages.friends.list.no_friends') }}</UiEmptyState><div v-else class="friends-table-wrap"><UiTable class="friends-table"><thead><tr><th>{{ $t('pages.friends.list.name') }}</th><th>{{ $t('pages.friends.list.alliance') }}</th><th>{{ $t('pages.friends.list.coordinates') }}</th><th>{{ $t('pages.friends.list.online') }}</th><th><span class="sr-only">{{ $t('pages.friends.actions') }}</span></th></tr></thead><tbody><FriendRow v-for="item in page.items" :key="item.id" :item="item"/></tbody></UiTable></div></UiPanel>
	</div>
</template>

<script setup>
	import { UiEmptyState, UiHeading, UiPanel, UiTable } from '~/components/UI';
	import FriendsNavigation from '~/components/Page/Friends/Navigation.vue';
	import FriendRow from '~/components/Page/Friends/FriendRow.vue';
	import { Head } from '@inertiajs/vue3';

	defineProps({
		page: Object,
	});
</script>