<template>
	<Head :title="$t('pages.alliance.info.head_title', [page['name']])"/>
	<div class="page-alliance page-alliance-overview">
		<AllianceBack/>
		<header class="alliance-hero">
			<div class="alliance-emblem"><img v-if="page.image" :src="page.image" :alt="page.name"><AllianceIcon v-else aria-hidden="true"/></div>
			<div class="alliance-identity"><span class="alliance-eyebrow">{{ $t('pages.alliance.info.page_heading') }}</span><h1><span class="alliance-tag">[{{ page.tag }}]</span> {{ page.name }}</h1></div>
		</header>
		<div class="alliance-metrics">
			<div><span>{{ $t('pages.alliance.info.label_members') }}</span><strong>{{ page.total_members }}</strong></div>
			<div v-if="page.web"><span>{{ $t('pages.alliance.info.label_website') }}</span><a :href="page.web" target="_blank" rel="noopener noreferrer">{{ page.web }}</a></div>
		</div>
		<section v-if="page.description" class="alliance-panel"><h2>{{ $t('pages.alliance.ui.about') }}</h2><div class="alliance-prose"><TextViewer :text="page.description"/></div></section>
		<div v-if="page.request" class="alliance-footer"><Link :href="'/alliance/join/' + page.id" class="button">{{ $t('pages.alliance.info.button_join') }}</Link></div>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import AllianceIcon from '~/images/icons/alliance.svg?component';
	import TextViewer from '~/components/TextViewer.vue';
	import { Head, Link } from '@inertiajs/vue3';

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