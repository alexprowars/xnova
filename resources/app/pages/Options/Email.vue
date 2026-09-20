<template>
	<Head :title="$t('pages.options.email_change_head_title')"/>
	<div class="page-options options-email">
		<Link href="/options" class="options-back">← {{ $t('pages.options.email_change_back') }}</Link>
		<header class="options-heading">
			<h1>{{ $t('pages.options.email_change_block_title') }}</h1>
		</header>
		<form class="options-card" method="post" @submit.prevent="update">
			<div v-if="Object.keys(form.errors).length" class="options-errors" role="alert">
				<div v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
			</div>
			<div class="options-row">
				<label for="options-email-password" class="options-label">{{ $t('pages.options.email_change_current_password') }}</label>
				<div class="options-control">
					<input id="options-email-password" type="password" name="password" v-model="form.password" :class="{error: v$.password.$error}" autocomplete="current-password">
				</div>
			</div>
			<div class="options-row">
				<label for="options-new-email" class="options-label">{{ $t('pages.options.email_change_new_email') }}</label>
				<div class="options-control">
					<input id="options-new-email" type="email" name="email" v-model="form.email" :class="{error: v$.email.$error}" autocomplete="email">
				</div>
			</div>
			<div class="options-actions">
				<button type="submit" class="button" :disabled="form.processing">{{ $t('pages.options.email_change_submit') }}</button>
			</div>
		</form>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core'
	import { required, email as emailValidation } from '@vuelidate/validators'
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const { t } = useI18n();

	const form = useForm({
		password: '',
		email: '',
	})

	const validations = {
		password: {
			required
		},
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

	async function update() {
		if (!await v$.value.$validate()) {
			return
		}

		form.post('/options/email', {
			onSuccess() {
				useSuccessNotification(t('pages.options.email_change_success'));
			}
		});
	}
</script>