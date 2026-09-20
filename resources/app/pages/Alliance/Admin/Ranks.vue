<template>
	<Head :title="$t('pages.alliance.admin.ranks_head_title')"/>
	<div class="page-alliance page-alliance-admin">
		<AllianceBack href="/alliance/admin"/>
		<header class="alliance-heading"><h1>{{ $t('pages.alliance.admin.ranks_head_title') }}</h1></header>
		<AllianceRanksForm :owner="user.id === page.alliance.user_id" :items="page.items"/>
		<AllianceRanksCreate/>
		<AllianceRanksInfo/>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import useState from '~/composables/useState.js';
	import AllianceRanksForm from '~/components/Page/Alliance/AllianceRanksForm.vue';
	import AllianceRanksInfo from '~/components/Page/Alliance/AllianceRanksInfo.vue';
	import AllianceRanksCreate from '~/components/Page/Alliance/AllianceRanksCreate.vue';
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