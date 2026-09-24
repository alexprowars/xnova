<template>
	<footer>
		<div class="hidden sm:block">
			<div class="w-full px-2 flex justify-between">
				<div>
					<Link href="https://github.com/alexprowars/xnova" target="_blank" :title="$t('footer.changes')">{{ state.version }}</Link>
					<a class="sm:hidden md:inline" target="_blank" href="https://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
				</div>
				<div>
					<a href="https://t.me/x_nova_game" target="_blank">Telegram</a>|
					<template v-if="state.user"><Link href="/support">{{ $t('menu.support') }}</Link>|</template>
					<Link href="/blocked">{{ $t('menu.blocked') }}</Link>|
					<Link href="/contacts">{{ $t('menu.contacts') }}</Link>|
					<Link href="/content/help">{{ $t('menu.help') }}</Link>|
					<Link href="/content/rules">{{ $t('menu.rules') }}</Link>|
					<a @click.prevent :title="$t('footer.online')" style="color: var(--ui-success-color)">{{ state.stats['online'] }}</a>/<a @click.prevent :title="$t('footer.total')" style="color: var(--ui-warning-color)">{{ state.stats['users'] }}</a>
				</div>
			</div>
		</div>
		<div class="sm:hidden footer-mobile">
			<nav class="footer-mobile-links" :aria-label="$t('interface.navigation')">
				<a href="https://t.me/x_nova_game" target="_blank">Telegram</a>
				<Link v-if="state.user" href="/support">{{ $t('menu.support') }}</Link>
				<Link href="/blocked">{{ $t('menu.blocked') }}</Link>
				<Link href="/contacts">{{ $t('menu.contacts') }}</Link>
				<Link href="/content/help">{{ $t('menu.help') }}</Link>
				<Link href="/content/rules">{{ $t('menu.rules') }}</Link>
			</nav>
			<div class="footer-mobile-meta">
				<div class="footer-mobile-credit">
					<Link href="https://github.com/alexprowars/xnova" target="_blank" :title="$t('footer.changes')">{{ state.version }}</Link>
					<a target="_blank" href="https://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
				</div>
				<div class="footer-mobile-stats">
					<span :title="$t('footer.online')">{{ state.stats['online'] }}</span>
					<span aria-hidden="true">/</span>
					<span :title="$t('footer.total')">{{ state.stats['users'] }}</span>
				</div>
			</div>
		</div>
	</footer>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { Link } from '@inertiajs/vue3';

	const state = useState();
</script>