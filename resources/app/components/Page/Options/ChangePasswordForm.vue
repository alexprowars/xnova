<template>
	<div class="block-table border-0! text-center">
		<div class="grid grid-cols-2">
			<div class="th middle">Старый пароль</div>
			<div class="th middle"><input name="current_password" v-model="currentPassword" :class="{error: v$.currentPassword.$error}" size="20" type="password" autocomplete="current-password"></div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th middle">Новый пароль (мин. 8 Знаков)</div>
			<div class="th middle"><input name="password" v-model="newPassword" :class="{error: v$.newPassword.$error}" size="20" maxlength="40" type="password" autocomplete="new-password"></div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th middle">Новый пароль (повтор)</div>
			<div class="th middle"><input name="password_confirmation" v-model="newPasswordConfirmation" :class="{error: v$.newPasswordConfirmation.$error}" size="20" maxlength="40" type="password" autocomplete="new-password"></div>
		</div>
		<div class="grid">
			<div class="th">
				<button type="button" class="button" @click.prevent="save">{{ $t('pages.options.save') }}</button>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core';
	import { required, sameAs } from '@vuelidate/validators';
	import { ref } from 'vue';
	import { useApiSubmit } from '~/composables/useApi.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { router } from '@inertiajs/vue3';

	const currentPassword = ref('');
	const newPassword = ref('');
	const newPasswordConfirmation = ref('');

	const validations = {
		currentPassword: {
			required
		},
		newPassword: {
			required,
		},
		newPasswordConfirmation: {
			required,
			sameAsPassword: sameAs(newPassword)
		},
	}

	const v$ = useVuelidate(
		validations,
		{ currentPassword, newPassword, newPasswordConfirmation },
		{ $autoDirty: true }
	);

	async function save() {
		if (!await v$.value.$validate()) {
			return
		}

		await useApiSubmit('/options/password', {
			current_password: currentPassword.value,
			password: newPassword.value,
			password_confirmation: newPasswordConfirmation.value,
		}, async () => {
			useSuccessNotification('Ваш Пароль успешно изменен');
			router.visit('/options');
		});
	}
</script>