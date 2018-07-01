<template>
	<div class="page-remind">
		<div v-if="error" v-html="error.message" :class="[error.type]" class="message"></div>
		<form action="" method="post" class="form" @submit.prevent="send">
			<div class="table">
				<div class="row">
					<div class="col th">
						Введите ваш Email, который вы указали при регистрации.
						При нажатии на кнопку "Получить пароль" на ваш e-mail будет выслана ссылка на новый пароль.
					</div>
				</div>
				<div class="row">
					<div class="col th">
						Ваш Email: <input :class="{error: $v.email.$error}" @change="$v.email.$touch()" type="email" name="email" v-model="email">
					</div>
				</div>
				<div class="row">
					<div class="col th">
						<input name="submit" type="submit" value="Выслать пароль">
					</div>
				</div>
			</div>
		</form>
	</div>
</template>

<script>
	import {required, email, minLength} from 'vuelidate/lib/validators'
	import { $post } from 'api'

	export default {
		name: "index-remind",
		data () {
			return {
				email: '',
				error: false
			}
		},
		validations: {
			email: {
				required,
				email
			},
		},
		methods: {
			send ()
			{
				this.$v.$touch();

				if (!this.$v.$invalid)
				{
					$post('/remind/', {
						'email': this.email
					})
					.then((result) =>
					{
						if (result.redirect && result.redirect.length)
							window.location.href = result.redirect;
						else
							this.error = result.error;
					})
				}
			}
		}
	}
</script>