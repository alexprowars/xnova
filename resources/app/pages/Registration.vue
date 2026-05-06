<template>
	<Head :title="$t('pages.registration.title')"/>
	<div class="page-registration">
		<div class="block">
			<div class="title">{{ $t('pages.registration.title') }}</div>
			<div class="content">
				<div v-for="error in errors" v-html="error" class="message error"></div>
				<form class="block-table form" action="" method="post" @submit.prevent="send">
					<div class="grid grid-cols-12">
						<div class="col-span-5 th middle">{{ $t('pages.registration.email') }}</div>
						<div class="col-span-7 th middle">
							<input :class="{error: v$.email.$error}" name="email" type="email" v-model="email" autocomplete="username">
						</div>
					</div>
					<div class="grid grid-cols-12">
						<div class="col-span-5 th middle">{{ $t('pages.registration.password') }}</div>
						<div class="col-span-7 th middle">
							<input :class="{error: v$.password.$error}" type="password" v-model="password" autocomplete="new-password">
						</div>
					</div>
					<div class="grid grid-cols-12">
						<div class="col-span-5 th middle">{{ $t('pages.registration.password_confirm') }}</div>
						<div class="col-span-7 th middle">
							<input :class="{error: v$.password_confirm.$error}" type="password" v-model="password_confirm" autocomplete="new-password">
						</div>
					</div>
					<ReCaptcha v-if="recaptchaKey" v-model="captchaToken"/>
					<div class="grid">
						<div class="th text-left">
							<input :class="{error: v$.rules.$error}" id="rules" type="checkbox" v-model="rules">
							<label for="rules">{{ $t('pages.registration.accept_rules') }}</label>
							<Link href="/content/agreement" target="_blank">{{ $t('pages.registration.user_agreement') }}</Link>
						</div>
					</div>
					<div class="grid">
						<div class="th text-left">
							<input :class="{error: v$.laws.$error}" id="laws" type="checkbox" v-model="laws">
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
	import { Head, Link, router } from '@inertiajs/vue3';
	import { useApiPost } from '../composables/useApi.js';

	const errors = ref([]);
	const email = ref('');
	const password = ref('');
	const password_confirm = ref('');
	const rules = ref(false);
	const laws = ref(false);
	const recaptchaKey = computed(() => import.meta.env.VITE_APP_NAME || null);
	const captchaToken = ref('');

	const validations = {
		email: {
			required,
			emailValidation,
		},
		password: {
			required,
			minLength: minLength(4),
		},
		password_confirm: {
			required,
			minLength: minLength(4),
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
		{ email, password, password_confirm, rules, laws },
		{ $autoDirty: true }
	);

	async function send () {
		if (!await v$.value.$validate()) {
			return
		}

		try {
			await useApiPost('/registration', {
				email: email.value,
				password: password.value,
				password_confirmation: password_confirm.value,
				captcha: captchaToken.value,
			});

			await useStore().loadState();

			router.visit('/start');
		} catch (e) {
			if (typeof e.item['errors'] !== 'undefined' && e.item['errors']) {
				errors.value = e.item['errors'];
			}

			window.grecaptcha?.enterprise.reset();
		}
	}
</script>