<template>
	<div class="admin-shell">
		<header class="admin-header">
			<button class="admin-button md:hidden" @click="open = !open" :aria-label="labels.menu" :aria-expanded="open">☰</button>
			<Link href="/admin" class="flex items-center gap-2 font-semibold tracking-wide text-base">
				<img src="/assets/images/pwa/icon_192.png" alt="" width="28" height="28" class="rounded-md"/>
				XNOVA
				<span class="admin-muted text-xs font-normal hidden sm:inline">
					/ {{ labels.admin }}
				</span>
			</Link>
			<div class="flex-1" />
			<span class="admin-muted hidden lg:inline">{{ admin.username }}</span>
			<a href="/overview" class="text-xs">↗ {{ labels.back_game }}</a>
			<select :value="page.props.locale" @change="
					router.post(
						'/admin/locale',
						{ locale: $event.target.value },
						{ preserveScroll: true },
					)
				" :aria-label="labels.language" style="width: 95px"
			>
				<option value="ru">Русский</option>
				<option value="en">English</option>
			</select>
		</header>
		<div class="admin-body">
			<aside class="admin-sidebar" :class="{ open }">
				<nav :aria-label="labels.menu">
					<p class="admin-muted px-3 mb-2 text-[10px] uppercase tracking-widest">
						{{ labels.workspace }}
					</p>
					<Link href="/admin" :aria-current="active('/admin') ? 'page' : undefined">
						{{ labels.dashboard }}
					</Link>
					<Link v-for="item in admin.navigation" :key="item.key" :href="item.url" :aria-current="active(item.url) ? 'page' : undefined">
						{{ item.label }}
					</Link>
				</nav>
			</aside>
			<main class="admin-main">
				<div v-if="admin.flash" class="admin-notice" role="status">{{ admin.flash }}</div>
				<slot />
			</main>
		</div>
	</div>
</template>

<script setup>
	import { computed, ref, watch } from 'vue';
	import { Link, router, usePage } from '@inertiajs/vue3';
	import { setLocale } from '~/i18n.js';

	const page = usePage();
	const admin = computed(() => page.props.admin);
	const labels = computed(() => admin.value.labels);
	const open = ref(false);

	const active = (url) => url === '/admin' ? page.url.split('?')[0] === '/admin' : page.url.startsWith(url);

	watch(
		() => page.props.locale,
		(value) => {
			setLocale(value);
			if (typeof document !== 'undefined') {
				document.documentElement.lang = value;
			}
		},
		{ immediate: true },
	);

	watch(
		() => page.url,
		() => {
			open.value = false;
		},
	);
</script>
