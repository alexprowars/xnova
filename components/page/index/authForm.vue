<template>
	<div>
		<div v-if="error" v-html="error.message" :class="[error.type]" class="message"></div>
		<form action="" method="post" @submit.prevent="send">
			<input :class="{error: $v.email.$error}" @change="$v.email.$touch()" name="email" class="input-text" placeholder="Email" v-model="email" type="email" autocomplete="username">
			<input :class="{error: $v.password.$error}" @change="$v.password.$touch()" name="password" class="input-text" placeholder="Пароль" v-model="password" type="password" autocomplete="current-password">
			<button type="submit" class="input-submit">Вход</button>
			<div class="remember">
				<input id="rememberme" type="checkbox" v-model="remember">
				<label for="rememberme">Запомнить меня</label>
			</div>
		</form>
	</div>
</template>

<script>
	import { required, email } from 'vuelidate/lib/validators'

	export default {
		name: "index-auth",
		data () {
			return {
				email: '',
				password: '',
				remember: false,
				error: false
			}
		},
		validations: {
			email: {
				required,
				email
			},
			password: {
				required
			}
		},
		methods: {
			send ()
			{
				this.$v.$touch();

				if (!this.$v.$invalid)
				{
					this.$post('/login/', {
						email: this.email,
						password: this.password,
						remember: this.remember,
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