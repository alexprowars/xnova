<template>
	<div class="page-registration">
		<div v-for="error in page.errors" v-html="error" class="message error"></div>
		<form action="" method="post" class="form" @submit.prevent="send">
			<div class="table">
				<div class="row">
					<div class="col-5 th">E-Mail<br>(используется для входа)</div>
					<div class="col-7 th">
						<input :class="{error: $v.email.$error}" @change="$v.email.$touch()" name="email" type="email" v-model="email" autocomplete="username">
					</div>
				</div>
				<div class="row">
					<div class="col-5 th">Пароль</div>
					<div class="col-7 th">
						<input :class="{error: $v.password.$error}" type="password" v-model="password" @change="$v.password.$touch()" autocomplete="new-password">
					</div>
				</div>
				<div class="row">
					<div class="col-5 th">Подтверждение пароля</div>
					<div class="col-7 th">
						<input :class="{error: $v.password_confirm.$error}" type="password" v-model="password_confirm" @change="$v.password_confirm.$touch()" autocomplete="new-password">
					</div>
				</div>
				<div class="row">
					<div class="col th text-center">
						<div ref="captcha" :data-sitekey="page['captcha']"></div>
					</div>
				</div>
				<div class="row">
					<div class="col th text-left">
						<input :class="{error: $v.rules.$error}" id="rules" type="checkbox" v-model="rules" @change="$v.rules.$touch()">
						<label for="rules">Я принимаю</label>
						<nuxt-link to="/content/agreement/" target="_blank">Пользовательское соглашение</nuxt-link>
					</div>
				</div>
				<div class="row">
					<div class="col th text-left">
						<input :class="{error: $v.laws.$error}" id="laws" type="checkbox" v-model="laws" @change="$v.laws.$touch()">
						<label for="laws">Я принимаю</label>
						<nuxt-link to="/content/agb/" target="_blank">Законы игры</nuxt-link>
					</div>
				</div>
				<div class="row">
					<div class="col th">
						<input name="submit" type="submit" value="Регистрация">
					</div>
				</div>
			</div>
		</form>
	</div>
</template>

<script>
	import { required, email, minLength } from 'vuelidate/lib/validators'

	export default {
		name: 'registration',
		props: {
			popup: {
				type: Object
			}
		},
		async asyncData ({ store })
		{
			const data = await store.dispatch('loadPage')

			return {
				data: data.page
			}
		},
		data () {
			return {
				page: {},
				email: '',
				password: '',
				password_confirm: '',
				rules: false,
				laws: false,
				captcha: null
			}
		},
		validations: {
			email: {
				required,
				email
			},
			password: {
				required,
				minLength: minLength(4)
			},
			password_confirm: {
				required,
				minLength: minLength(4)
			},
			rules: {
				required (val) {
					return val;
				}
			},
			laws: {
				required (val) {
					return val;
				}
			}
		},
		created () {
			this.page = this.popup !== undefined ? this.popup : this.data
		},
		mounted ()
		{
			this.captcha = grecaptcha.render(this.$refs['captcha'], {
				sitekey: this.page['captcha']
			})
		},
		methods: {
			send ()
			{
				this.$v.$touch();

				if (!this.$v.$invalid)
				{
					this.$post('/registration/', {
						'email': this.email,
						'password': this.password,
						'password_confirm': this.password_confirm,
						'captcha': grecaptcha.getResponse(this.captcha)
					})
					.then((result) =>
					{
						if (result.redirect && result.redirect.length)
							window.location.href = result.redirect;
						else
						{
							grecaptcha.reset(this.captcha)
							this.page = result.page;
						}
					})
				}
			}
		}
	}
</script>