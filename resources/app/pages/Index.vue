<template>
	<Head :title="$t('pages.index.meta_title')"/>
	<div class="page-index">
		<a class="landing-skip-link" href="#landing-login">{{ $t('pages.index.go_to_login') }}</a>
		<div class="landing-shell">
			<header class="landing-header">
				<Link href="/" class="game-brand" aria-label="XNova">
					<img :src="brandLogo" class="game-brand-logo" width="152" height="40" alt="" aria-hidden="true">
				</Link>
				<LanguageSwitcher/>
				<nav class="landing-nav" :aria-label="$t('interface.navigation')">
					<Link href="/stats">{{ $t('menu.stats') }}</Link>
					<Link href="/content/rules">{{ $t('menu.rules') }}</Link>
					<a href="https://t.me/x_nova_game" target="_blank" rel="noopener noreferrer">Telegram <span aria-hidden="true">↗</span></a>
				</nav>
				<a class="landing-login-link" href="#landing-login">{{ $t('pages.index.auth_submit') }} <span aria-hidden="true">→</span></a>
			</header>

			<main class="landing-main">
				<section class="landing-hero" aria-labelledby="landing-title">
					<div class="landing-intro">
						<div class="landing-eyebrow"><span aria-hidden="true"></span>{{ $t('interface.sector') }}</div>
						<h1 id="landing-title">{{ $t('pages.index.headline_start') }} <em>{{ $t('pages.index.headline_accent') }}</em></h1>
						<p class="landing-description">{{ $t('interface.intro') }}</p>
						<div class="landing-actions">
							<button type="button" class="landing-start" @click="showRegistration">
								{{ $t('interface.start') }} <span aria-hidden="true">↗</span>
							</button>
							<span class="landing-browser-note">{{ $t('pages.index.browser_note') }}</span>
						</div>
						<dl v-if="state.stats.online != null || state.stats.users != null" class="landing-stats">
							<div v-if="state.stats.online != null">
								<dt>{{ $t('pages.index.stats_online_tooltip') }}</dt>
								<dd><span class="landing-online-dot" aria-hidden="true"></span>{{ $n(state.stats.online) }}</dd>
							</div>
							<div v-if="state.stats.users != null">
								<dt>{{ $t('pages.index.stats_total_tooltip') }}</dt>
								<dd>{{ $n(state.stats.users) }}</dd>
							</div>
						</dl>
					</div>

					<section id="landing-login" class="landing-login" aria-labelledby="landing-login-title" tabindex="-1">
						<div class="landing-login-caption"><span class="landing-eyebrow">{{ $t('interface.command') }}</span><span aria-hidden="true">01 / XN</span></div>
						<h2 id="landing-login-title">{{ $t('interface.welcome') }}</h2>
						<p class="landing-login-hint">{{ $t('interface.login_hint') }}</p>
						<div class="landing-login-inputs"><AuthForm/></div>
						<button class="landing-forgot" type="button" @click="showRemindPassword" :title="$t('pages.index.remind_password_title')">{{ $t('pages.index.forgot_password') }}</button>
						<div class="landing-social">
							<span>{{ $t('pages.index.social_login_label') }}</span>
							<a href="/login/social/vkid"><span class="landing-vk-mark" aria-hidden="true">VK</span> VK ID <span aria-hidden="true">↗</span></a>
						</div>
						<p class="landing-register-note">{{ $t('pages.index.new_commander') }} <button type="button" @click="showRegistration">{{ $t('pages.index.registration_cta') }}</button></p>
					</section>
				</section>

				<section class="landing-features" :aria-label="$t('pages.index.features_label')">
					<article v-for="(feature, index) in features" :key="feature.key" class="landing-feature">
						<div class="landing-feature-heading"><component :is="feature.icon" aria-hidden="true"/><span>0{{ index + 1 }}</span></div>
						<h2>{{ $t('pages.index.' + feature.key + '_title') }}</h2>
						<p>{{ $t('pages.index.' + feature.key + '_description') }}</p>
					</article>
				</section>
			</main>

			<footer class="landing-footer">
				<div class="landing-copyright">&copy; {{ (new Date).getFullYear() }} XNOVA<span>{{ $t('pages.index.footer_desk') }}</span></div>
				<nav :aria-label="$t('pages.index.useful_links')">
					<Link href="/sim">{{ $t('menu.sim') }}</Link>
					<Link href="/blocked">{{ $t('menu.blocked') }}</Link>
					<Link href="/contacts">{{ $t('menu.contacts') }}</Link>
				</nav>
			</footer>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import brandLogo from '~/images/brand.png';
	import CityIcon from '~/images/icon-city.svg?component';
	import RadarIcon from '~/images/icons/radar.svg?component';
	import AllianceIcon from '~/images/icons/alliance.svg?component';
	import AuthForm from '~/components/Page/Index/AuthForm.vue';
	import LanguageSwitcher from '~/components/Layout/LanguageSwitcher.vue';
	import { isMobile } from '~/utils/helpers';
	import { Head, Link, router } from '@inertiajs/vue3';
	import { visitModal } from '@inertiaui/modal-vue';
	import App from '~/App.vue';

	defineOptions({
		layout: [App],
	});

	const state = useState();
	const features = [
		{ key: 'build', icon: CityIcon },
		{ key: 'explore', icon: RadarIcon },
		{ key: 'unite', icon: AllianceIcon },
	];

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
</script>