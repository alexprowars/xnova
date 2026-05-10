<template>
	<footer>
		<div class="hidden sm:block">
			<div class="w-full px-2 flex justify-between">
				<div>
					<Link href="https://github.com/alexprowars/xnova" target="_blank" title="Последние изменения">{{ state.version }}</Link>
					<a class="sm:hidden md:inline" target="_blank" href="https://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
				</div>
				<div>
					<a href="https://t.me/x_nova_game" target="_blank">Telegram</a>|
					<Link href="/support">{{ $t('menu.support') }}</Link>|
					<Link href="/blocked">{{ $t('menu.blocked') }}</Link>|
					<Link href="/contacts">{{ $t('menu.contacts') }}</Link>|
					<Link href="/content/help">{{ $t('menu.help') }}</Link>|
					<Link href="/content/rules">{{ $t('menu.rules') }}</Link>|
					<a @click.prevent title="Игроков в сети" style="color:green">{{ state.stats['online'] }}</a>/<a @click.prevent title="Всего игроков" style="color:yellow">{{ state.stats['users'] }}</a>
				</div>
			</div>
		</div>
		<div class="sm:hidden footer-mobile">
			<div class="text-center mb-4">
				<a href="https://t.me/x_nova_game" target="_blank">Telegram</a>|
				<Link href="/support">{{ $t('menu.support') }}</Link>|
				<Link href="/blocked">{{ $t('menu.blocked') }}</Link>|
				<Link href="/contacts">{{ $t('menu.contacts') }}</Link>|
				<Link href="/content/help">{{ $t('menu.help') }}</Link>|
				<Link href="/content/rules">{{ $t('menu.rules') }}</Link>
			</div>
			<div class="w-full px-2 flex justify-between">
				<div>
					<Link href="https://github.com/alexprowars/xnova" target="_blank" title="Последние изменения">{{ state.version }}</Link>
					<a class="media_1" target="_blank" href="https://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
				</div>
				<div>
					<a @click.prevent title="Игроков в сети" style="color:green">{{ state.stats['online'] }}</a>/<a @click.prevent title="Всего игроков" style="color:yellow">{{ state.stats['users'] }}</a>
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