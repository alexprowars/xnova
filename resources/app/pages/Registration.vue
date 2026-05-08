<template>
	<div class="page-registration">
		<Head :title="$t('pages.registration.title')"/>
		<div class="block">
			<div class="title">{{ $t('pages.registration.title') }}</div>
			<div class="content">
				<div v-for="error in form.errors" v-html="error" class="message error"></div>
				<form class="block-table form" action="" method="post" @submit.prevent="send">
					<div class="grid grid-cols-12">
						<div class="col-span-5 th middle">{{ $t('pages.registration.email') }}</div>
						<div class="col-span-7 th middle">
							<input :class="{error: v$.email.$error}" name="email" type="email" v-model="form.email" autocomplete="username">
						</div>
					</div>
					<div class="grid grid-cols-12">
						<div class="col-span-5 th middle">{{ $t('pages.registration.password') }}</div>
						<div class="col-span-7 th middle">
							<input :class="{error: v$.password.$error}" type="password" v-model="form.password" autocomplete="new-password">
						</div>
					</div>
					<div class="grid grid-cols-12">
						<div class="col-span-5 th middle">{{ $t('pages.registration.password_confirm') }}</div>
						<div class="col-span-7 th middle">
							<input :class="{error: v$.password_confirmation.$error}" type="password" v-model="form.password_confirmation" autocomplete="new-password">
						</div>
					</div>
					<ReCaptcha v-if="recaptchaKey" v-model="captchaToken"/>
					<div class="grid">
						<div class="th text-left">
							<input :class="{error: v$.rules.$error}" id="rules" type="checkbox" v-model="form.rules">
							<label for="rules">{{ $t('pages.registration.accept_rules') }}</label>
							<Link href="/content/agreement" target="_blank">{{ $t('pages.registration.user_agreement') }}</Link>
						</div>
					</div>
					<div class="grid">
						<div class="th text-left">
							<input :class="{error: v$.laws.$error}" id="laws" type="checkbox" v-model="form.laws">
							<label for="laws">{{ $t('pages.registration.accept_rules') }}</label>
							<Link href="/content/agb" target="_blank">{{ $t('pages.registration.game_rules') }}</Link>
						</div>
					</div>
					<div class="grid">
						<div class="th text-center">
							<button type="submit" class="button">
								{{ $t('pages.registration.submit_button') }}
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core'
	import { required, email as emailValidation, minLength } from '@vuelidate/validators'
	import { computed, ref } from 'vue';
	import ReCaptcha from '../components/ReCaptcha.vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';

	const recaptchaKey = computed(() => import.meta.env.VITE_APP_NAME || null);
	const captchaToken = ref('');

	const form = useForm({
		email: '',
		password: '',
		password_confirm: '',
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