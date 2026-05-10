<template>
	<form method="post" @submit.prevent="changeRace">
		<select v-model="form.race">
			<option value="">выбрать...</option>
			<option value="1">Конфедерация</option>
			<option value="2">Бионики</option>
			<option value="3">Сайлоны</option>
			<option value="4">Древние</option>
		</select>
		<br><br>
		<button v-if="form.race" type="submit" class="button">Сменить фракцию</button>
	</form>
</template>

<script setup>
	import { ref } from 'vue';
	import { useForm } from '@inertiajs/vue3';

	const form = useForm({
		race: '',
	});

	const race = ref('');

	async function changeRace() {
		form.post('/race/change', {
			preserveUrl: true,
		});
	}
</script>
