<template>
	<Head title="Восстановление пароля"/>
	<div class="flex justify-center">
		<div class="block w-full max-w-3xl">
			<div class="title">Изменение пароля</div>
			<div class="content">
				<form class="block-table text-center" method="post" action="" @submit.prevent="send">
					<div v-if="form.errors" class="grid">
						<template v-for="error in form.errors">
							<div v-html="error" class="th error message"></div>
						</template>
					</div>
					<div class="grid grid-cols-4">
						<div class="th">Email</div>
						<div class="th col-span-3">
							{{ email }}
						</div>
					</div>
					<div class="grid grid-cols-4">
						<div class="th middle">Новый пароль</div>
						<div class="th col-span-3">
							<input :class="{error: v$.password.$error}" id="auth_password" name="password" type="password" autocomplete="new-password" class="input-text" v-model="form.password">
						</div>
					</div>
					<div class="grid grid-cols-4">
						<div class="th middle">Подтверждение пароля</div>
						<div class="th col-span-3">
							<input :class="{error: v$.password_confirmation.$error}" id="auth_password2" name="password_confirmation" type="password" autocomplete="new-password" class="input-text" v-model="form.password_confirmation">
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<button type="submit" class="button">Изменить</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script setup>
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
		if (!await v$.value.$validate()) {
			return;
		}

		form.post('/login/reset');
	}
</script>