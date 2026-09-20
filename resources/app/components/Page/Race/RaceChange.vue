<template>
	<form class="race-change-form" method="post" @submit.prevent="changeRace">
		<label for="new-race">{{ $t('pages.race.new_faction') }}</label>
		<div class="race-change-controls">
			<select id="new-race" v-model="form.race" required>
				<option value="" disabled>{{ $t('pages.race.choose_faction') }}</option>
				<option v-for="id in 4" :key="id" :value="id" :disabled="id === state.user.race">{{ $t('races.' + id) }}</option>
			</select>
			<button type="submit" class="button" :disabled="!form.race || form.processing">{{ $t('pages.race.change_title') }}</button>
		</div>
		<div v-if="Object.keys(form.errors).length" class="race-change-errors" role="alert"><span v-for="(error, key) in form.errors" :key="key">{{ error }}</span></div>
	</form>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { useForm } from '@inertiajs/vue3';

	const state = useState();
	const form = useForm({ race: '' });

	function changeRace() {
		if (!form.race || form.processing) return;

		form.post('/race/change', {
			preserveUrl: true,
		});
	}
</script>