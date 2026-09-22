<template>
	<Head :title="page.status + ' — ' + title"/>
	<div class="page-error-full">
		<div class="error-shell">
			<header class="error-header">
				<Link href="/" class="game-brand" aria-label="XNova">
					<img :src="brandLogo" class="game-brand-logo" width="152" height="40" alt="" aria-hidden="true">
				</Link>
				<span>{{ $t('interface.sector') }}</span>
			</header>

			<main class="error-main">
				<section class="error-heading" aria-labelledby="error-title">
					<div class="error-eyebrow">{{ $t('pages.errors.signal_lost') }}</div>
					<div class="error-code">{{ page.status }}</div>
					<h1 id="error-title">{{ title }}</h1>
					<p class="error-description">{{ page.status === 404 ? $t('pages.errors.lost_description') : page.message }}</p>
					<UiButton :as="Link" href="/" class="error-home">{{ $t('pages.errors.home') }} <span aria-hidden="true">↗</span></UiButton>
					<p class="error-return-hint">{{ $t('pages.errors.return_hint') }}</p>
				</section>
			</main>

			<div class="error-footer">XNOVA · {{ $t('interface.sector') }}</div>
		</div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { Head, Link } from '@inertiajs/vue3';
	import { UiButton } from '~/components/UI';
	import brandLogo from '~/images/brand.png';
	import App from '~/App.vue';

	defineOptions({
		layout: [App],
	});

	const props = defineProps({
		page: Object,
	});

	const { t } = useI18n();
	const title = computed(() => t(props.page.status === 404 ? 'pages.errors.not_found' : 'pages.errors.unavailable'));
</script>
