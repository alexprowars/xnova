<template>
	<div class="options-password">
		<div v-if="Object.keys(form.errors).length" class="options-errors" role="alert">
			<div v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
		</div>
		<div class="options-row">
			<label for="options-current_password" class="options-label">{{ $t('pages.options.current_password') }}</label>
			<div class="options-control">
				<input id="options-current_password" name="current_password" v-model="form.current_password" :class="{error: v$.current_password.$error}" type="password" autocomplete="current-password">
			</div>
		</div>
		<div class="options-row">
			<label for="options-password" class="options-label">{{ $t('pages.options.new_password') }}</label>
			<div class="options-control">
				<input id="options-password" name="password" v-model="form.password" :class="{error: v$.password.$error}" type="password" autocomplete="new-password" maxlength="40">
			</div>
		</div>
		<div class="options-row">
			<label for="options-password_confirmation" class="options-label">{{ $t('pages.options.repeat_password') }}</label>
			<div class="options-control">
				<input id="options-password_confirmation" name="password_confirmation" v-model="form.password_confirmation" :class="{error: v$.password_confirmation.$error}" type="password" autocomplete="new-password" maxlength="40">
			</div>
		</div>
		<div class="options-actions">
			<button type="button" class="button" @click.prevent="save" :disabled="form.processing">{{ $t('pages.options.save') }}</button>
		</div>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core';
	import { required, sameAs } from '@vuelidate/validators';
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useForm } from '@inertiajs/vue3';

	const { t } = useI18n();
	const form = useForm({
		current_password: '',
		password: '',
		password_confirmation: '',
	});

	const validations = {
		current_password: {
			required
		},
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

	async function save() {
		if (!await v$.value.$validate()) {
			return
		}

		form.post('/options/password', {
			preserveUrl: true,
			onSuccess() {
				useSuccessNotification(t('pages.options.password_saved'));
			}
		});
	}
</script>