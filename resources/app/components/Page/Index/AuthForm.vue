<template>
	<div>
		<div v-if="errors.error" v-html="errors.error" class="message error"></div>
		<form action="" method="post" @submit.prevent="send">
			<input :class="{error: v$.email.$error}" name="email" class="input-text" placeholder="Email" v-model="form.email" type="email" autocomplete="username">
			<input :class="{error: v$.password.$error}" name="password" class="input-text" :placeholder="$t('pages.index.auth_password_placeholder')" v-model="form.password" type="password" autocomplete="current-password">
			<button type="submit" class="button input-submit">{{ $t('pages.index.auth_submit') }}</button>
			<div class="remember">
				<input id="rememberme" type="checkbox" v-model="form.remember">
				<label for="rememberme">{{ $t('pages.index.auth_remember_me') }}</label>
			</div>
		</form>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core';
	import { required, email as emailValidation } from '@vuelidate/validators';
	import { computed } from 'vue';
	import { useForm, usePage } from '@inertiajs/vue3';

	const page = usePage();
	const errors = computed(() => page.props.errors || {});

	const form = useForm('AuthForm', {
		email: '',
		password: '',
		remember: true,
	}).dontRemember(['password']);

	const validations = {
		email: {
			required,
			emailValidation
		},
		password: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function send () {
		if (!await v$.value.$validate()) {
			return
		}

		form.post('/login');
	}
</script>