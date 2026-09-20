<template>
	<section class="alliance-panel">
		<h2>{{ $t('pages.alliance.ui.create_rank') }}</h2>
		<form class="alliance-form" @submit.prevent="save">
			<label for="alliance-rank-name">{{ $t('pages.alliance.ui.rank_name') }}</label>
			<div class="alliance-search-field">
				<input id="alliance-rank-name" type="text" v-model="form.name" :class="{error: v$.name.$error}" maxlength="30">
				<button type="submit" class="button" :disabled="form.processing">{{ $t('pages.alliance.create.submit') }}</button>
			</div>
			<div v-if="v$.name.$error" class="alliance-errors">{{ $t('pages.alliance.ui.required') }}</div>
			<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
		</form>
	</section>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import { useForm } from '@inertiajs/vue3';

	const form = useForm({
		name: '',
	})

	const validations = {
		name: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function save() {
		if (form.processing) return;

		if (!await v$.value.$validate()) {
			return
		}

		form.post('/alliance/admin/ranks/create', {
			onSuccess() {
				form.reset();
				v$.value.$reset();
			}
		});
	}
</script>