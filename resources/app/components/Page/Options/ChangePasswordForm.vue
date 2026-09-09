<template>
	<div class="block-table border-0! text-center">
		<div class="grid grid-cols-2">
			<div class="th middle">Старый пароль</div>
			<div class="th middle"><input name="current_password" v-model="form.current_password" :class="{error: v$.current_password.$error}" size="20" type="password" autocomplete="current-password"></div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th middle">Новый пароль (мин. 8 Знаков)</div>
			<div class="th middle"><input name="password" v-model="form.password" :class="{error: v$.password.$error}" size="20" maxlength="40" type="password" autocomplete="new-password"></div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th middle">Новый пароль (повтор)</div>
			<div class="th middle"><input name="password_confirmation" v-model="form.password_confirmation" :class="{error: v$.password_confirmation.$error}" size="20" maxlength="40" type="password" autocomplete="new-password"></div>
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
	import { computed } from 'vue';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useForm } from '@inertiajs/vue3';

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
				useSuccessNotification('Ваш Пароль успешно изменен');
			}
		});
	}
</script>