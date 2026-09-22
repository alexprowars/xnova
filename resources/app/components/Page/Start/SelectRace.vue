<template>
	<UiPanel class="game-panel">
		<header class="start-heading">
			<span class="game-eyebrow">02 / 03</span>
			<h2>{{ $t('pages.start.race_selection') }}</h2>
		</header>
		<form class="game-form" @submit.prevent="next">
			<div class="race-grid">
				<RaceCard v-for="faction in factions" :key="faction.id" :faction="faction" selectable :selected="form.race === faction.id" @select="selectRace"/>
			</div>
			<div v-if="form.errors.race" class="game-errors">{{ form.errors.race }}</div>
			<div class="game-actions">
				<UiButton variant="secondary" :disabled="form.processing" @click="emit('back')">← {{ $t('pages.start.back') }}</UiButton>
				<UiButton type="submit" :disabled="!form.race || form.processing">{{ $t('pages.start.continue') }} →</UiButton>
			</div>
		</form>
	</UiPanel>
</template>

<script setup>
	import { UiButton, UiPanel } from '~/components/UI';
	import RaceCard from '~/components/Page/Race/RaceCard.vue';
	import factions from '~/components/Page/Race/factions.js';

	const props = defineProps({
		form: { type: Object, required: true },
	});
	const emit = defineEmits(['back', 'next']);

	function selectRace(race) {
		props.form.race = race;
		props.form.clearErrors('race');
	}

	function next() {
		if (props.form.processing || !props.form.race) return;

		emit('next');
	}
</script>