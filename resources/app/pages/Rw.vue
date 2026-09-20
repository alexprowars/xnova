<template>
	<Head :title="$t('pages.sim.battle_report')"/>
	<div class="game-page page-battle-report">
		<UiHeading :title="$t('pages.sim.battle_report')" class="game-heading"/>
		<div v-if="page" v-html="page.raport"></div>
	</div>
</template>

<script setup>
	import { UiHeading } from '~/components/UI';
	import App from '~/App.vue';
	import EmptyLayout from '~/layouts/EmptyLayout.vue';
	import { Head } from '@inertiajs/vue3';
	import DefaultLayout from '~/layouts/DefaultLayout.vue';

	defineProps({
		page: Object,
	});

	defineOptions({
		layout: (props) => props.state.user ? [App, DefaultLayout] : [App, EmptyLayout],
	});
</script>