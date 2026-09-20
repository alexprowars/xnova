<template>
	<Head :title="$t('pages.auth.recovery')"/>
	<div class="game-page page-auth">
		<UiPanel class="game-panel">
			<header class="auth-heading">
				<span class="game-eyebrow">XNova</span>
				<h1>{{ $t('pages.auth.new_password') }}</h1>
				<p>{{ email }}</p>
			</header>
			<form class="game-form" @submit.prevent="send">
				<input type="hidden" name="email" autocomplete="username" :value="email">
				<label>
					{{ $t('pages.auth.new_password') }}
					<input
						name="password"
						type="password"
						autocomplete="new-password"
						:class="{error: v$.password.$error}"
						v-model="form.password"
					>
				</label>
				<label>
					{{ $t('pages.registration.password_confirm') }}
					<input
						name="password_confirmation"
						type="password"
						autocomplete="new-password"
						:class="{error: v$.password_confirmation.$error}"
						v-model="form.password_confirmation"
					>
				</label>
				<div v-if="v$.$error" class="game-errors">{{ $t('pages.auth.password_match') }}</div>
				<div v-for="(error, key) in form.errors" :key="key" class="game-errors">{{ error }}</div>
				<div class="game-actions">
					<UiButton type="submit" :disabled="form.processing">{{ $t('pages.auth.change_password') }}</UiButton>
				</div>
			</form>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiPanel } from '~/components/UI';
	import { required, sameAs } from '@vuelidate/validators';
	import { useVuelidate } from '@vuelidate/core';
	import { computed } from 'vue';
	import { Head, useForm } from '@inertiajs/vue3';
	import App from '~/App.vue';
	import EmptyLayout from '~/layouts/EmptyLayout.vue';
	import { useUrlSearchParams } from '@vueuse/core';

	defineOptions({
		layout: [App, EmptyLayout],
	});

	const params = useUrlSearchParams('history');
	const email = computed(() => params['email'] || '');

	const form = useForm({
		token: params['token'] || '',
		email: email.value,
		password: '',
		password_confirmation: '',
	});

	const validations = {
		password: {
			required,
		},
		password_confirmation: {
			required,
			sameAsPassword: sameAs(computed(() => form.password))
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

		form.post('/login/reset');
	}
</script>