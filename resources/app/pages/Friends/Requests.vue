<template>
	<Head :title="isMy ? $t('pages.friends.tab_outgoing') : $t('pages.friends.tab_incoming')"/>
	<div class="page-friends">
		<UiHeading :title="isMy ? $t('pages.friends.tab_outgoing') : $t('pages.friends.tab_incoming')" :count="page.items.length"/>
		<FriendsNavigation :active="isMy ? 'outgoing' : 'incoming'"/>
		<UiPanel clip><UiEmptyState v-if="!page.items.length">{{ $t('pages.friends.requests.no_requests') }}</UiEmptyState><div v-else class="friends-requests"><RequestRow v-for="item in page.items" :key="item.id" :item="item" :is-my="isMy"/></div></UiPanel>
	</div>
</template>

<script setup>
	import { UiEmptyState, UiHeading, UiPanel } from '~/components/UI';
	import FriendsNavigation from '~/components/Page/Friends/Navigation.vue';
	import RequestRow from '~/components/Page/Friends/RequestRow.vue';
	import { computed } from 'vue';
	import { Head } from '@inertiajs/vue3';

	const props = defineProps({
		page: Object,
	});

	const isMy = computed(() => props.page.isMy);
</script>