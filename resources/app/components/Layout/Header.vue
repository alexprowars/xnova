<template>
	<header class="top-menu">
		<div class="top-menu-container">
			<Link href="/overview" class="game-brand" aria-label="XNova">
				<img :src="brandLogo" class="game-brand-logo" width="152" height="40" alt="" aria-hidden="true">
			</Link>
			<div class="top-menu-block left">
				<Popper v-if="user['quests'] < 10" :content="$t('menu.quests')">
					<Link href="/quests" class="m1" :aria-label="$t('menu.quests')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-book"></use>
						</svg>
						<b>{{ 10 - user['quests'] }}</b>
					</Link>
				</Popper>
				<Popper :content="$t('menu.chat')">
					<Link href="/chat" class="m1" :aria-label="$t('menu.chat')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-chat"></use>
						</svg>
					</Link>
				</Popper>
				<Popper :content="$t('menu.messages')">
					<Link href="/messages" class="m1" :aria-label="$t('menu.messages')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-message"></use>
						</svg>
						<b v-if="user.messages > 0">{{ user.messages }}</b>
					</Link>
				</Popper>
				<Popper v-if="user.alliance" :content="$t('menu.alliance-chat')">
					<Link href="/alliance/chat" class="m1" :aria-label="$t('menu.alliance-chat')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-alliance"></use>
						</svg>
						<b v-if="user.alliance.messages > 0">{{ user.alliance.messages }}</b>
					</Link>
				</Popper>
			</div>
			<Clock class="game-clock"/>
			<LanguageSwitcher/>
			<div class="top-menu-block right">
				<Popper :content="$t('menu.stats')">
					<Link href="/stats" class="m1" :aria-label="$t('menu.stats')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-statistics"></use>
						</svg>
					</Link>
				</Popper>
				<Popper :content="$t('menu.tech')">
					<Link href="/tech" class="m1" :aria-label="$t('menu.tech')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-tech"></use>
						</svg>
					</Link>
				</Popper>
				<Popper :content="$t('menu.sim')">
					<Link href="/sim" class="m1" :aria-label="$t('menu.sim')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-sim"></use>
						</svg>
					</Link>
				</Popper>
				<Popper :content="$t('menu.search')">
					<Link href="/search" class="m1" :aria-label="$t('menu.search')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-search"></use>
						</svg>
					</Link>
				</Popper>
				<Popper :content="$t('menu.options')">
					<Link href="/options" class="m1" :aria-label="$t('menu.options')">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-settings"></use>
						</svg>
					</Link>
				</Popper>
				<Popper :content="$t('menu.logout')">
					<a href="" @click.prevent="logout" class="m1" :aria-label="$t('menu.logout')">
						<svg class="icon red">
							<use xlink:href="/assets/images/symbols.svg#icon-exit"></use>
						</svg>
					</a>
				</Popper>
			</div>
		</div>
	</header>
</template>

<script setup>
	import Popper from '~/components/Popper.vue';
	import Clock from './Clock.vue';
	import LanguageSwitcher from './LanguageSwitcher.vue';
	import brandLogo from '~/images/brand.png';
	import useState from '~/composables/useState.js';
	import { Link, router } from '@inertiajs/vue3';
	import { computed } from 'vue';

	const state = useState();
	const user = computed(() => state.user);

	function logout() {
		router.post('/logout');
	}
</script>