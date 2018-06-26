<template>
	<div class="page-index">
		<div class="left">
			<div class="middle">
				<div class="loginform">
					<div class="login">Вход в игру:</div>

					<div class="login-inputs">
						<index-auth></index-auth>
					</div>
					<div class="lost-pass">
						<a @click.prevent="showRemindPassword" title="Восстановление пароля">Забыли пароль?</a>
					</div>
					<div class="sm">
						Войти с помощью:<br><br>
						<div id="uLogin" data-uloginid="e4860195" :x-ulogin-params="'display=panel;fields=first_name,last_name,photo;providers=vkontakte,odnoklassniki,facebook,twitter,yandex,googleplus,mailru;redirect_uri=http%3A%2F%2F'+$store.state['host']+'%2F'"></div>
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
		<div style="position: absolute;bottom: 30px;right: 10px;">
			<a href="//www.free-kassa.ru/"><img src="//www.free-kassa.ru/img/fk_btn/17.png"></a>
		</div>
		<div class="bottom">
			<div class="desk">Звездная Империя - онлайн-игра</div>
			<div class="nav">
				<a href="http://forum.xnova.su" title="Официальный форум" target="_blank">Форум</a>  |
				<router-link to="/xnsim/">Симулятор</router-link>  |
				<router-link to="/stat/">Статистика</router-link>  |
				<a href="//vkontakte.ru/xnova_game" title="Официальная группа ВКонтакте" target="_blank">ВКонтакте</a>  |
				<router-link to="/content/agb/">Правила</router-link>  |
				<router-link to="/banned/">Блокировки</router-link>  |
				<router-link to="/contact/">Администрация</router-link>
			</div>
			<div class="copy">
				<a @click.prevent title="Игроков в сети" style="color:green">{{ $store.state['stats']['online'] }}</a> / <a @click.prevent title="Всего игроков" style="color:yellow">{{ $store.state['stats']['users'] }}</a>&nbsp;&nbsp;&nbsp;&copy; {{ (new Date).getFullYear() }} XNOVA.SU
			</div>
		</div>
		<div id="mask"></div>
	</div>
</template>

<script>
	import IndexAuth from './index-auth.vue'
	import { addScript } from 'helpers'

	export default {
		name: "index",
		components: {
			IndexAuth
		},
		methods: {
			showRegistration () {
				this.$root.openPopup('Регистрация', '/registration/', (window.orientation !== undefined ? 300 : 600), 400);
			},
			showRemindPassword () {
				this.$root.openPopup('Восстановление пароля', '/remind/', (window.orientation !== undefined ? 300 : 600), 200);
			}
		},
		mounted() {
			addScript('https://ulogin.ru/js/ulogin.js');
			addScript('https://www.google.com/recaptcha/api.js');
		}
	}
</script>