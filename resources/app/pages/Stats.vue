<template>
	<Head :title="$t('pages.stats.title')"/>
	<div class="page-stat">
		<div class="block">
			<div class="title text-center">
				{{ $t('pages.stats.header', [$formatDate(page['update'], 'DD MMM YYYY HH:mm:ss')]) }}
			</div>
			<div class="content">
				<div class="block-table text-center">
					<div class="grid grid-cols-12">
						<div class="th col-span-2 middle">{{ $t('pages.stats.table_header_stats') }}</div>
						<div class="th col-span-4 sm:col-span-2">
							<select v-model="form.list">
								<option value="players">{{ $t('pages.stats.list_players') }}</option>
								<option value="alliances">{{ $t('pages.stats.list_alliances') }}</option>
								<option value="races">{{ $t('pages.stats.list_races') }}</option>
							</select>
						</div>
						<div class="th col-span-2 sm:col-span-1 middle">{{ $t('pages.stats.label_by') }}</div>
						<div class="th col-span-4 sm:col-span-3">
							<select v-model="form.type">
								<option :value="1">{{ $t('pages.stats.type_points') }}</option>
								<option :value="2">{{ $t('pages.stats.type_fleet') }}</option>
								<option :value="5">{{ $t('pages.stats.type_buildings') }}</option>
								<option :value="3">{{ $t('pages.stats.type_research') }}</option>
								<option :value="4">{{ $t('pages.stats.type_defense') }}</option>
								<option v-if="form.list !== 'races'" :value="6">{{ $t('pages.stats.type_peace_level') }}</option>
								<option v-if="form.list !== 'races'" :value="7">{{ $t('pages.stats.type_combat_level') }}</option>
							</select>
						</div>
						<div v-if="form.list !== 'races'" class="th col-span-2 middle">{{ $t('pages.stats.label_place') }}</div>
						<div v-if="form.list !== 'races'" class="th col-span-10 sm:col-span-2">
							<select v-model="form.page">
								<option v-for="i in form.pages" :value="i">{{ (i - 1) * 100 + 1 }} - {{ i * 100 }}</option>
							</select>
						</div>
					</div>
				</div>
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
