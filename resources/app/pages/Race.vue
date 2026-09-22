<template>
	<Head :title="$t('pages.race.head_title')"/>
	<div class="page-race">
		<header class="race-heading">
			<h1>{{ $t('pages.race.head_title') }}</h1>
			<span v-if="race">{{ $t('pages.race.your_faction') }}: <strong>{{ $t('races.' + race) }}</strong></span>
		</header>
		<div class="race-grid">
			<article v-for="faction in factions" :key="faction.id" class="race-card" :class="['race-card-' + faction.id, { 'is-current': race === faction.id }]">
				<div class="race-card-heading">
					<RaceIcon :code="faction.id" class="race-emblem" aria-hidden="true" focusable="false"/>
					<div class="race-card-intro">
						<h2>{{ $t('pages.race.' + faction.name) }}</h2>
						<p class="race-description">{{ $t('pages.race.description_race' + faction.id) }}</p>
						<span v-if="race === faction.id" class="race-current">
							<CheckIcon stroke-width="1.8" aria-hidden="true"/>
							{{ $t('pages.race.your_faction') }}
						</span>
					</div>
				</div>
				<div class="race-card-content">
					<h3>{{ $t('pages.race.race_features') }}</h3>
					<ul class="race-perks">
						<li v-for="(perk, index) in $t('pages.race.perks_race' + faction.id).split('<br>')" :key="index">{{ perk }}</li>
					</ul>
				</div>
				<ModalLink navigate :href="'/info/' + faction.ship" class="race-ship">
					<img :src="'/assets/images/elements/' + faction.ship + '.webp'" alt="" width="44" height="44" loading="lazy">
					<div>
						<span class="race-ship-label">{{ $t('pages.race.unique_ship_label') }}</span>
						<strong>{{ $t('pages.race.ship_race' + faction.id + '_name') }}</strong>
						<span class="race-ship-description">{{ $t('pages.race.ship_race' + faction.id + '_desc') }}</span>
					</div>
					<RightIcon stroke-width="1.6" aria-hidden="true"/>
				</ModalLink>
			</article>
		</div>
		<section v-if="page.change_available" class="race-change-panel">
			<div class="race-change-heading">
				<h2>{{ $t('pages.race.change_title') }}</h2>
				<span>{{ page.change ? $t('pages.race.change_free', { count: page.change }) : $t('pages.race.change_paid') }}</span>
			</div>
			<div class="race-change-content">
				<p>{{ $t('pages.race.change_requirements') }}</p>
				<RaceChange/>
			</div>
		</section>
	</div>
</template>

<script setup>
	import CheckIcon from '~/images/icons/check.svg?component';
	import RightIcon from '~/images/icons/right.svg?component';
	import useState from '~/composables/useState.js';
	import RaceIcon from '~/components/RaceIcon.vue';
	import RaceChange from '~/components/Page/Race/RaceChange.vue';
	import { computed, nextTick, onMounted } from 'vue';
	import { Head } from '@inertiajs/vue3';
	import { ModalLink, visitModal } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const factions = [
		{ id: 1, name: 'faction_confederation', ship: 220 },
		{ id: 2, name: 'faction_bionics', ship: 221 },
		{ id: 3, name: 'faction_cylons', ship: 222 },
		{ id: 4, name: 'faction_ancients', ship: 223 },
	];

	const state = useState();
	const user = computed(() => state.user);
	const race = computed(() => user.value?.race || 0);

	onMounted(() => {
		if (race.value) {
			return;
		}

		nextTick(() => {
			visitModal('/content/welcome');
		})
	});
</script>