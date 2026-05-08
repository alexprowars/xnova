<template>
	<Head :title="$t('pages.logs.view.page_title')"/>
	<div id="report" class="table-responsive" v-html="raport"></div>
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
		raport: String,
	})
</script>