<template>
	<Head title="Восстановление пароля"/>
	<div class="page-remind">
		<div class="block">
			<div class="title">Восстановление пароля</div>
			<div class="content">
				<div v-if="error" v-html="error.message" :class="[error.type]" class="message"></div>
				<form class="block-table form text-center" method="post" @submit.prevent="send">
					<div class="grid">
						<div class="th">
							Введите ваш Email, который вы указали при регистрации.
							При нажатии на кнопку "Получить пароль" на ваш e-mail будет выслана ссылка на новый пароль.
						</div>
					</div>
					<div class="grid">
						<div class="th">
							Ваш Email: <input :class="{error: v$.email.$error}" type="email" name="email" v-model="email">
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
	import { ref } from 'vue';
	import { Head } from '@inertiajs/vue3';

	const email = ref('');
	const error = ref();

	const validations = {
		email: {
			required,
			emailValidation
		},
	}

	const v$ = useVuelidate(
		validations,
		{ email },
		{ $autoDirty: true }
	);

	async function send () {
		if (!await v$.value.$validate()) {
			return
		}

		try {
			const result = await useApiPost('/login/forgot', {
				email: email.value,
			});

			email.value = '';
			error.value = { message: result['message'], type: 'success' }
		} catch (e) {
			error.value = { message: e.message, type: 'error' }
		}
	}
</script>