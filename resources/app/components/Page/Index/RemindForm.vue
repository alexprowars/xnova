<template>
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
						Ваш Email: <input :class="{error: v$.email.$error}" type="email" name="email" v-model="form.email">
					</div>
				</div>
				<div class="grid">
					<div class="th">
						<button type="submit" class="button">Выслать пароль</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core'
	import { required, email as emailValidation } from '@vuelidate/validators'
	import { useForm, useHttp } from '@inertiajs/vue3';
	import { ref } from 'vue';

	const form = useForm({
		email: '',
	});

	const error = ref(null);

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
			return;
		}

		useHttp(form).post('/login/forgot', {
			onSuccess(result) {
				error.value = result;
			}
		});
	}
</script>