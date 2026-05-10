<template>
	<Head :title="$t('pages.index.meta_title')"/>
	<div class="page-index">
		<div class="left">
			<div class="middle">
				<div class="loginform">
					<div class="login">{{ $t('pages.index.login_heading') }}</div>

					<div class="login-inputs">
						<AuthForm/>
					</div>
					<div class="lost-pass">
						<a @click.prevent="showRemindPassword" :title="$t('pages.index.remind_password_title')">{{ $t('pages.index.forgot_password') }}</a>
					</div>
					<div class="sm">
						{{ $t('pages.index.social_login_label') }}<br><br>

						<a href="" @click.prevent="socialLogin('vkontakte')">Vkontakte</a>
					</div>
				</div>
			</div>
		</div>
		<div class="right">
			<div class="middle">
				<div class="text">
					<h1>{{ $t('pages.index.intro_heading') }}</h1>

					<p>{{ $t('pages.index.intro_paragraph_1') }}</p>
					<p>{{ $t('pages.index.intro_paragraph_2') }}</p>

					<div id="reg_button" @click.prevent="showRegistration"><a>{{ $t('pages.index.registration_cta') }}</a></div>
				</div>
			</div>
		</div>
		<div class="bottom">
			<div class="desk">{{ $t('pages.index.footer_desk') }}</div>
			<div class="nav">
				<a href="https://t.me/x_nova_game" target="_blank">Telegram</a>  |
				<Link href="/xnsim" external>{{ $t('menu.sim') }}</Link>  |
				<Link href="/stats">{{ $t('menu.stats') }}</Link>  |
				<Link href="/content/rules">{{ $t('menu.rules') }}</Link>  |
				<Link href="/blocked">{{ $t('menu.blocked') }}</Link>  |
				<Link href="/contacts">{{ $t('menu.contacts') }}</Link>
			</div>
			<div v-if="page.props['stats']" class="copy">
				<a @click.prevent :title="$t('pages.index.stats_online_tooltip')" style="color:green">{{ page.props['stats']['online'] }}</a> / <a @click.prevent :title="$t('pages.index.stats_total_tooltip')" style="color:yellow">{{ page.props['stats']['users'] }}</a>&nbsp;&nbsp;&nbsp;&copy; {{ (new Date).getFullYear() }} XNOVA.SU
			</div>
		</div>
	</div>
</template>

<script setup>
	import AuthForm from '~/components/Page/Index/AuthForm.vue';
	import { isMobile } from '~/utils/helpers';
	import { Head, Link, router, usePage } from '@inertiajs/vue3';
	import { visitModal } from '@inertiaui/modal-vue'

	const page = usePage();

	function showRegistration () {
		if (isMobile()) {
			return router.visit('/registration');
		}

		visitModal('/registration');
	}

	function showRemindPassword () {
		if (isMobile()) {
			return router.visit('/remind');
		}

		visitModal('/remind');
	}

	async function socialLogin(service) {
		window.location.href = '/login/social/' + service;
	}
</script>