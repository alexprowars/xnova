<template>
	<Head :title="$t('pages.options.title')"/>
	<div class="page-options">
		<header class="options-heading"><h1>{{ $t('pages.options.title') }}</h1><span>{{ user.name }}</span></header>
		<VacationTab v-if="user.vacation"/>
		<OptionsTab v-else :data="page"/>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import VacationTab from '~/components/Page/Options/VacationTab.vue';
	import OptionsTab from '~/components/Page/Options/OptionsTab.vue';
	import { Head } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	})

	const state = useState();
	const user = computed(() => state.user);
</script>