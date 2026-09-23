<template>
	<header class="top-menu guest-header">
		<div class="top-menu-container">
			<Link href="/" class="game-brand" aria-label="XNova">
				<img :src="brandLogo" class="game-brand-logo" width="152" height="40" alt="" aria-hidden="true">
			</Link>
			<LanguageSwitcher/>
			<nav class="guest-nav" :aria-label="$t('interface.navigation')">
				<Link href="/stats">{{ $t('menu.stats') }}</Link>
				<Link href="/content/rules">{{ $t('menu.rules') }}</Link>
				<Link href="/contacts">{{ $t('menu.contacts') }}</Link>
				<Link href="/" class="guest-login">{{ $t('pages.index.auth_submit') }}</Link>
			</nav>
		</div>
	</header>
</template>

<script setup>
	import brandLogo from '~/images/brand.png';
	import LanguageSwitcher from './LanguageSwitcher.vue';
	import { Link } from '@inertiajs/vue3';
</script>
