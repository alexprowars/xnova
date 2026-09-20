<template>
	<UiTabNavigation :items="tabs" :active="active" :label="$t('pages.friends.navigation')"/>
</template>

<script setup>
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { UiTabNavigation } from '~/components/UI';

	defineProps({
		active: String,
	});
	const { t } = useI18n();
	const tabs = computed(() => [
		{ id: 'list', href: '/friends', label: t('pages.friends.tab_friends') },
		{ id: 'incoming', href: '/friends/requests', label: t('pages.friends.tab_incoming') },
		{ id: 'outgoing', href: '/friends/requests/my', label: t('pages.friends.tab_outgoing') },
	]);
</script>