<template>
	<div class="page-remind">
		<Head title="Восстановление пароля"/>
		<div class="block">
			<div class="title">Восстановление пароля</div>
			<div class="content">
				<template v-for="error in form.errors">
					<div v-html="error.message" :class="[error.type]" class="message"></div>
				</template>
				<form class="block-table form text-center" method="post" @submit.prevent="send">
					<div class="grid">
						<div class="th">
							Введите ваш Email, который вы указали при регистрации.
							При нажатии на кнопку "Получить пароль" на ваш e-mail будет выслана ссылка на новый пароль.
						</div>
					</div>
					<div class="grid">
						<div class="th">
							Ваш Email: <input :class="{error: v$.email.$error}" type="email" name="email" v-model="form.email">
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<button type="submit">Выслать пароль</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core'
	import { required, email as emailValidation } from '@vuelidate/validators'
	import { Head, useForm } from '@inertiajs/vue3';
	import { useApiPost } from '../composables/useApi.js';

	const form = useForm({
		email: '',
	});

	const validations = {
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

	async function send () {
		if (!await v$.value.$validate()) {
			return
		}

		//try {
		//await useApiPost('/login/forgot', form.data());
		//} catch (error) {
		//	console.log(error)
		//}

		//return;

		form.post('/login/forgot', {
			onSuccess() {
				form.reset();
			}
		})

		//try {
		//	email.value = '';
		//	error.value = { message: result['message'], type: 'success' }
		//} catch (e) {
		//	error.value = { message: e.message, type: 'error' }
		//}
	}
</script>