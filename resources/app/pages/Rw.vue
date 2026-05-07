<template>
	<Head title="Боевой доклад"/>
	<div v-if="page" v-html="page['raport']"></div>
</template>

<script setup>
	import App from '../App.vue';
	import EmptyLayout from '../layouts/EmptyLayout.vue';
	import { Head } from '@inertiajs/vue3';
	import DefaultLayout from '../layouts/DefaultLayout.vue';

	defineOptions({
		layout: (props) => props.user ? [App, DefaultLayout] : [App, EmptyLayout],
	});
</script>