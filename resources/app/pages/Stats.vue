<template>
	<Head :title="$t('pages.stats.title')"/>
	<div class="page-stat">
		<div class="block stats-toolbar">
			<div class="title stats-heading"><span>{{ $t('pages.stats.title') }}</span><span class="stats-updated">{{ $t('pages.stats.updated_at', { time: $formatDate(page.update, 'DD MMM YYYY HH:mm:ss') }) }}</span></div>
			<div class="stats-filters">
				<label><span>{{ $t('pages.stats.table_header_stats') }}</span><select v-model="form.list"><option value="players">{{ $t('pages.stats.list_players') }}</option><option value="alliances">{{ $t('pages.stats.list_alliances') }}</option><option value="races">{{ $t('pages.stats.list_races') }}</option></select></label>
				<label><span>{{ $t('pages.stats.rating_by') }}</span><select v-model="form.type">
					<option :value="1">{{ $t('pages.stats.type_points') }}</option><option :value="2">{{ $t('pages.stats.type_fleet') }}</option><option :value="5">{{ $t('pages.stats.type_buildings') }}</option><option :value="3">{{ $t('pages.stats.type_research') }}</option><option :value="4">{{ $t('pages.stats.type_defense') }}</option><option v-if="form.list === 'players'" :value="6">{{ $t('pages.stats.type_peace_level') }}</option><option v-if="form.list === 'players'" :value="7">{{ $t('pages.stats.type_combat_level') }}</option>
				</select></label>
				<label v-if="form.list !== 'races'"><span>{{ $t('pages.stats.players_table_rank') }}</span><select v-model="form.page"><option v-for="i in form.pages" :key="i" :value="i">{{ (i - 1) * 100 + 1 }} – {{ i * 100 }}</option></select></label>
			</div>
		</div>

		<StatPlayers v-if="page.list === 'players'" :items="page.items"/>
		<StatAlliances v-if="page.list === 'alliances'" :items="page.items"/>
		<StatRaces v-if="page.list === 'races'" :items="page.items"/>
	</div>
</template>

<script setup>
	import StatPlayers from '~/components/Page/Stat/Players.vue';
	import StatAlliances from '~/components/Page/Stat/Alliances.vue';
	import StatRaces from '~/components/Page/Stat/Races.vue';
	import { ref, watch } from 'vue';
	import { Head, router } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	});

	const form = ref({
		list: props.page.list ?? 'players',
		type: props.page.type ?? 1,
		page: props.page.page ?? 1,
		pages: Math.max(Math.ceil((props.page.elements ?? 0) / 100), 1),
	});

	watch(() => form.value.list, () => {
		form.value.type = 1;
		form.value.page = 1;
	});

	watch(() => form.value.type, () => {
		form.value.page = 1;
	});

	watch(form, (value) => {
		router.get('/stats/' + value.list, {
			type: value.type,
			page: value.page,
		}, {
			preserveScroll: true,
			replace: true,
		});
	}, { deep: true });
</script>