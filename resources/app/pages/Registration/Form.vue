<template>
	<Head :title="$t('pages.registration.title')"/>
	<div class="game-page page-auth">
		<UiPanel class="game-panel">
			<header class="auth-heading">
				<span class="game-eyebrow">XNova</span>
				<h1>{{ $t('pages.registration.title') }}</h1>
				<p>{{ $t('pages.auth.register_hint') }}</p>
			</header>
			<form class="game-form" @submit.prevent="send">
				<label>
					{{ $t('pages.registration.email') }}
					<input :class="{error: v$.email.$error}" name="email" type="email" v-model="form.email" autocomplete="username">
				</label>
				<label>
					{{ $t('pages.registration.password') }}
					<input
						:class="{error: v$.password.$error}"
						name="password"
						type="password"
						v-model="form.password"
						autocomplete="new-password"
					>
				</label>
				<label>
					{{ $t('pages.registration.password_confirm') }}
					<input
						:class="{error: v$.password_confirmation.$error}"
						name="password_confirmation"
						type="password"
						v-model="form.password_confirmation"
						autocomplete="new-password"
					>
				</label>
				<ReCaptcha v-if="recaptchaKey" v-model="form.captcha"/>
				<div class="auth-agreements">
					<label :class="{ 'has-error': v$.rules.$error }">
						<input type="checkbox" v-model="form.rules">
						<span>{{ $t('pages.registration.accept_rules') }} <Link href="/content/agreement" target="_blank">{{ $t('pages.registration.user_agreement') }}</Link></span>
					</label>
					<label :class="{ 'has-error': v$.laws.$error }">
						<input type="checkbox" v-model="form.laws">
						<span>{{ $t('pages.registration.accept_rules') }} <Link href="/content/agb" target="_blank">{{ $t('pages.registration.game_rules') }}</Link></span>
					</label>
				</div>
				<div v-if="v$.$error" class="game-errors">{{ $t('pages.auth.check_fields') }}</div>
				<div v-for="(error, key) in form.errors" :key="key" class="game-errors">{{ error }}</div>
				<div class="game-actions">
					<UiButton type="submit" :disabled="form.processing">{{ $t('pages.registration.submit_button') }}</UiButton>
				</div>
			</form>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiPanel } from '~/components/UI';
	import { useVuelidate } from '@vuelidate/core'
	import { required, email as emailValidation, minLength } from '@vuelidate/validators'
	import { computed } from 'vue';
	import ReCaptcha from '~/components/ReCaptcha.vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';

	const recaptchaKey = computed(() => import.meta.env.VITE_APP_NAME || null);

	const form = useForm({
		email: '',
		password: '',
		password_confirmation: '',
		captcha: null,
		rules: null,
		laws: null,
	});

	const validations = {
		email: {
			required,
			emailValidation,
		},
		password: {
			required,
			minLength: minLength(6),
		},
		password_confirmation: {
			required,
			minLength: minLength(6),
		},
		rules: {
			required,
		},
		laws: {
			required,
		}
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

		form.post('/registration', {
			onSuccess() {
				window.grecaptcha?.enterprise.reset();
			}
		});
	}
</script>