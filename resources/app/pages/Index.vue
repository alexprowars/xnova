<template>
	<Head :title="$t('pages.index.meta_title')"/>
	<div class="page-index">
		<div class="landing-header">
			<Link href="/" class="game-brand" aria-label="XNova"><span class="game-brand-mark" aria-hidden="true">X</span><span>NOVA</span></Link>
			<span class="eyebrow">{{ $t('interface.sector') }}</span>
		</div>
		<div class="right">
			<div class="middle">
				<div class="text">
					<span class="eyebrow">{{ $t('interface.sector') }}</span>
					<h1>{{ $t('interface.headline') }}</h1>

					<p>{{ $t('interface.intro') }}</p>
					<div class="landing-features"><span>{{ $t('menu.buildings') }}</span><span>{{ $t('menu.fleet') }}</span><span>{{ $t('menu.alliance') }}</span></div>

					<button type="button" id="reg_button" @click="showRegistration">{{ $t('interface.start') }} <span aria-hidden="true">↗</span></button>
				</div>
			</div>
		</div>
		<div class="left">
			<div class="middle">
				<div class="loginform">
					<span class="eyebrow">{{ $t('pages.index.meta_title') }}</span>
					<h2 class="login">{{ $t('interface.welcome') }}</h2>
					<p class="login-hint">{{ $t('interface.login_hint') }}</p>

					<div class="login-inputs">
						<AuthForm/>
					</div>
					<div class="lost-pass">
						<button type="button" @click="showRemindPassword" :title="$t('pages.index.remind_password_title')">{{ $t('pages.index.forgot_password') }}</button>
					</div>
					<div class="sm">
						{{ $t('pages.index.social_login_label') }}

						<a href="" @click.prevent="socialLogin('vkid')">Vkontakte</a>
					</div>
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
			<div v-if="state['stats']" class="copy">
				<a @click.prevent :title="$t('pages.index.stats_online_tooltip')" style="color:green">{{ state['stats']['online'] }}</a> / <a @click.prevent :title="$t('pages.index.stats_total_tooltip')" style="color:yellow">{{ state['stats']['users'] }}</a>&nbsp;&nbsp;&nbsp;&copy; {{ (new Date).getFullYear() }} XNOVA.SU
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import AuthForm from '~/components/Page/Index/AuthForm.vue';
	import { isMobile } from '~/utils/helpers';
	import { Head, Link, router } from '@inertiajs/vue3';
	import { visitModal } from '@inertiaui/modal-vue';
	import App from '~/App.vue';

	defineOptions({
		layout: [App],
	});

	const state = useState();

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