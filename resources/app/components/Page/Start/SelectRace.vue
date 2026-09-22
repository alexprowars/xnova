<template>
	<UiPanel class="game-panel">
		<header class="start-heading">
			<span class="game-eyebrow">02 / 02</span>
			<h1>{{ $t('pages.start.race_selection') }}</h1>
		</header>
		<form class="game-form" @submit.prevent="save">
			<div class="start-race-grid">
				<label v-for="(race_id, index) in Object.keys($tm('races'))" :key="race_id" class="start-race-card" :class="{ 'is-selected': form.race === race_id }">
					<input type="radio" :value="race_id" v-model="form.race" :aria-label="$t('races.' + race_id)">
					<div class="start-race-title">
						<RaceIcon :code="race_id" aria-hidden="true"/>
						<h2>{{ $t('races.' + race_id) }}</h2><span v-if="form.race === race_id" aria-hidden="true">✓</span>
					</div>
					<div class="start-race-description" v-html="$t('info.' + (701 + index))"></div>
				</label>
			</div>
			<div v-for="(error, key) in form.errors" :key="key" class="game-errors">{{ error }}</div>
			<div class="game-actions">
				<UiButton type="submit" :disabled="!form.race || form.processing">{{ $t('pages.start.continue') }} →</UiButton>
			</div>
		</form>
	</UiPanel>
</template>

<script setup>
	import { UiButton, UiPanel } from '~/components/UI';
	import RaceIcon from '~/components/RaceIcon.vue';
	import { useForm } from '@inertiajs/vue3';

	const form = useForm({
		race: null,
	});

	async function save() {
		if (form.processing) return;

		form.post('/start/race', {
			preserveUrl: true,
		});
	}
</script>