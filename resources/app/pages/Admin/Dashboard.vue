<template>
	<Head :title="page.props.admin.labels.dashboard" />
	<h1>{{ page.props.admin.labels.dashboard }}</h1>
</template>

<script setup>
	import { Head, usePage } from '@inertiajs/vue3';

	const page = usePage();
</script>
