<template>
	<Head :title="$t('pages.logs.view.page_title')"/>
	<div class="game-page page-battle-report"><UiHeading :title="$t('pages.logs.view.page_title')" class="game-heading"/><div id="report" v-html="page.raport"></div></div>
</template>

<script setup>
	import { UiHeading } from '~/components/UI';
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
</script>