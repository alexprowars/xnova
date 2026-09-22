<template>
	<Head :title="$t('pages.race.head_title')"/>
	<div class="page-race">
		<header class="race-heading">
			<h1>{{ $t('pages.race.head_title') }}</h1>
			<span v-if="race">{{ $t('pages.race.your_faction') }}: <strong>{{ $t('races.' + race) }}</strong></span>
		</header>
		<div class="race-grid">
			<RaceCard v-for="faction in factions" :key="faction.id" :faction="faction" :selected="race === faction.id"/>
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
	import useState from '~/composables/useState.js';
	import RaceCard from '~/components/Page/Race/RaceCard.vue';
	import RaceChange from '~/components/Page/Race/RaceChange.vue';
	import factions from '~/components/Page/Race/factions.js';
	import { computed, nextTick, onMounted } from 'vue';
	import { Head } from '@inertiajs/vue3';
	import { visitModal } from '@inertiaui/modal-vue';

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