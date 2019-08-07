<template>
	<div class="page-index">
		<div class="left">
			<div class="middle">
				<div class="loginform">
					<div class="login">Вход в игру:</div>

					<div class="login-inputs">
						<AuthForm/>
					</div>
					<div class="lost-pass">
						<a @click.prevent="showRemindPassword" title="Восстановление пароля">Забыли пароль?</a>
					</div>
					<div class="sm">
						Войти с помощью:<br><br>

						<no-ssr>
							<div id="uLogin" data-uloginid="e4860195" :x-ulogin-params="'display=panel;fields=first_name,last_name,photo;providers=vkontakte,odnoklassniki,facebook,twitter,yandex,googleplus,mailru;redirect_uri=http%3A%2F%2F'+$store.state['host']+'%2F'"></div>
						</no-ssr>
					</div>
				</div>
			</div>
		</div>
		<div class="right">
			<div class="middle">
				<div class="text">
					<h1>Звездная Империя - это браузерная игра в жанре космическая стратегия</h1>

					<p>Захватывающие битвы, множество альянсов, нескончаемый игровой мир, тысячи противников,
					-&nbsp;это неполный список того, что вам предстоит испытать на себе в космической стратегии XNova.</p>

					<p>Завоёвывайте планеты, покоряйте галактики, создайте нерушимый альянс сильнейших игроков!
					Сойдитесь в неравной космической битве со своими противниками, окунувшись в зрелищный и захватывающий мир XNova!</p>

					<div id="reg_button" @click.prevent="showRegistration"><a>Регистрация</a></div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div class="bottom">
			<div class="desk">Звездная Империя - онлайн-игра</div>
			<div class="nav">
				<a href="http://forum.xnova.su" title="Официальный форум" target="_blank">Форум</a>  |
				<nuxt-link to="/xnsim/">Симулятор</nuxt-link>  |
				<nuxt-link to="/stat/">Статистика</nuxt-link>  |
				<a href="//vkontakte.ru/xnova_game" title="Официальная группа ВКонтакте" target="_blank">ВКонтакте</a>  |
				<nuxt-link to="/content/agb/">Правила</nuxt-link>  |
				<nuxt-link to="/banned/">Блокировки</nuxt-link>  |
				<nuxt-link to="/contacts/">Администрация</nuxt-link>
			</div>
			<div v-if="$store.state['stats']" class="copy">
				<a @click.prevent title="Игроков в сети" style="color:green">{{ $store.state['stats']['online'] }}</a> / <a @click.prevent title="Всего игроков" style="color:yellow">{{ $store.state['stats']['users'] }}</a>&nbsp;&nbsp;&nbsp;&copy; {{ (new Date).getFullYear() }} XNOVA.SU
			</div>
		</div>
		<div id="mask"></div>
	</div>
</template>

<script>
	import AuthForm from '~/components/page/index/authForm.vue'
	import RegistrationForm from './registration.vue'
	import RemindForm from './remind.vue'

	import { addScript } from '~/utils/helpers'

	export default {
		name: 'index',
		components: {
			AuthForm
		},
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		methods: {
			showRegistration ()
			{
				if (this.$store.getters.isMobile)
					return window.location.href = '/registration/'

				this.$get('/registration/').then((data) =>
				{
					this.$modal.show(RegistrationForm, {
						popup: data.page
					}, {
						width: 600,
						height: 'auto'
					})
				});
			},
			showRemindPassword ()
			{
				if (this.$store.getters.isMobile)
					return window.location.href = '/remind/'

				this.$get('/remind/').then((data) =>
				{
					this.$modal.show(RemindForm, {
						popup: data.page
					}, {
						width: 600,
						height: 'auto'
					})
				});
			}
		},
		mounted () {
			addScript('https://ulogin.ru/js/ulogin.js');
			addScript('https://www.google.com/recaptcha/api.js');
		}
	}
</script>