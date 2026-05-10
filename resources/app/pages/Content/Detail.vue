<template>
	<Head :title="page.title"/>
	<div class="block">
		<div v-if="page.title" class="title" v-html="page.title"></div>
		<div class="content p-1" v-html="page.body"></div>
	</div>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	});
</script>