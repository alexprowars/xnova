<template>
	<div class="game-page page-error page-error-full"><UiPanel class="game-panel"><header class="error-heading"><span class="error-code">{{ page.status }}</span><h1 v-if="page.status === 404">{{ $t('pages.errors.not_found') }}</h1><div class="game-prose">{{ page.message }}</div><UiButton :as="Link" href="/">{{ $t('pages.errors.home') }}</UiButton></header><div class="error-game"><Game/></div></UiPanel></div>
</template>

<script setup>
	import { UiButton, UiPanel } from '~/components/UI';
	import { Link } from '@inertiajs/vue3';
	import Game from '~/components/Layout/Game.vue';
	import App from '~/App.vue';
	import EmptyLayout from '~/layouts/EmptyLayout.vue';

	defineOptions({
		layout: [App, EmptyLayout],
	});

	defineProps({
		page: Object,
	});
</script>