<template>
	<div class="game-page page-auth page-remind"><UiPanel class="game-panel"><header class="auth-heading"><span class="game-eyebrow">XNova</span><h1>{{ $t('pages.auth.recovery') }}</h1><p>{{ $t('pages.auth.recovery_hint') }}</p></header>
		<form class="game-form" @submit.prevent="send">
			<div v-if="error" :class="['auth-notice', error.type]" role="status" v-html="error.message"></div>
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
	import { useHttp } from '@inertiajs/vue3';
	import { ref } from 'vue';

	const form = useHttp({
		email: '',
	});

	const error = ref(null);

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
			return;
		}

		form.post('/login/forgot', {
			onSuccess(result) {
				error.value = result;
			}
		});
	}
</script>