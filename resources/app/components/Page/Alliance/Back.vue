<template>
	<Link :href="href" class="alliance-back"><ArrowLeftIcon aria-hidden="true"/>{{ $t(href === '/alliance/admin' ? 'pages.alliance.admin.main_heading' : 'pages.alliance.ui.back') }}</Link>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
	import ArrowLeftIcon from '~/images/icons/arrow-left.svg?component';

	defineProps({ href: { type: String, default: '/alliance' } });
</script>