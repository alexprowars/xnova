<template>
	<Head :title="$t('pages.auth.recovery')"/>
	<div class="game-page page-auth page-remind"><UiPanel class="game-panel"><header class="auth-heading"><span class="game-eyebrow">XNova</span><h1>{{ $t('pages.auth.recovery') }}</h1><p>{{ $t('pages.auth.recovery_hint') }}</p></header>
		<form class="game-form" @submit.prevent="send">
			<label>{{ $t('pages.registration.email') }}<input type="email" name="email" autocomplete="email" :class="{error: v$.email.$error}" v-model="form.email"></label>
			<div v-if="v$.email.$error" class="game-errors">{{ $t('pages.auth.valid_email') }}</div><div v-for="(error, key) in form.errors" :key="key" class="game-errors">{{ error.message || error }}</div>
			<div class="game-actions"><UiButton type="submit" :disabled="form.processing">{{ $t('pages.auth.send_link') }}</UiButton></div>
		</form>
	</UiPanel></div>
</template>

<script setup>
	import { UiButton, UiPanel } from '~/components/UI';
	import { useVuelidate } from '@vuelidate/core'
	import { required, email as emailValidation } from '@vuelidate/validators'
	import { Head, useForm } from '@inertiajs/vue3';

	const form = useForm({
		email: '',
	});

	const validations = {
		email: {
			required,
			emailValidation
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function send () {
		if (form.processing) return;

		if (!await v$.value.$validate()) {
			return
		}

		//try {
		//await useApiPost('/login/forgot', form.data());
		//} catch (error) {
		//	console.log(error)
		//}

		//return;

		form.post('/login/forgot', {
			onSuccess() {
				form.reset();
			}
		})

		//try {
		//	email.value = '';
		//	error.value = { message: result['message'], type: 'success' }
		//} catch (e) {
		//	error.value = { message: e.message, type: 'error' }
		//}
	}
</script>