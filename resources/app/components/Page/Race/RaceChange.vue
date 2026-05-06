<template>
	<form method="post" @submit.prevent="changeRace">
		<select v-model="race">
			<option value="">выбрать...</option>
			<option value="1">Конфедерация</option>
			<option value="2">Бионики</option>
			<option value="3">Сайлоны</option>
			<option value="4">Древние</option>
		</select>
		<br><br>
		<button v-if="race" type="submit">Сменить фракцию</button>
	</form>
</template>

<script setup>
	import { ref } from 'vue';
	import { router } from '@inertiajs/vue3';
	import { useErrorNotification } from '../../../composables/useToast.js';
	import { useApiPost } from '../../../composables/useApi.js';

	const race = ref('');

	async function changeRace() {
		try {
			await useApiPost('/race/change', {
				race
			});

			router.visit('/overview');
		} catch (e) {
			useErrorNotification(e.message);
		}
	}
</script>