<template>
	<Link class="ui-back" :href="href">
		<ArrowLeftIcon aria-hidden="true"/>
		<slot/>
	</Link>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
	import ArrowLeftIcon from '~/images/icons/arrow-left.svg?component';

	defineProps({
		href: { type: String, required: true },
	});
</script>
