<template>
	<Head :title="$t('pages.alliance.admin.ranks_head_title')"/>
	<div>
		<AllianceRanksForm :owner="user['id'] === data['alliance']['user_id']" :items="data['items']"/>
		<AllianceRanksCreate/>
		<AllianceRanksInfo/>
		<div class="mt-2">
			<Link href="/alliance/admin" class="button">
				{{ $t('pages.alliance.admin.nav_back_admin_hub') }}
			</Link>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import AllianceRanksForm from '~/components/Page/Alliance/AllianceRanksForm.vue';
	import AllianceRanksInfo from '~/components/Page/Alliance/AllianceRanksInfo.vue';
	import AllianceRanksCreate from '~/components/Page/Alliance/AllianceRanksCreate.vue';
	import { Head, Link } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		data: Object,
	})

	const state = useState();
	const user = computed(() => state.user);
</script>