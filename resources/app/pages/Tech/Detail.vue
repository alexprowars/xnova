<template>
	<Head :title="page.name"/>
	<div class="page-tech-detail">
		<Link href="/tech" class="tech-back-link"><span aria-hidden="true">←</span> {{ $t('pages.techtree.all_technologies') }}</Link>
		<header class="tech-detail-heading">
			<img :src="'/assets/images/elements/' + page.id + '.webp'" alt="" width="72" height="72">
			<div class="tech-detail-identity">
				<h1>{{ page.name }}</h1>
				<div class="tech-detail-meta"><span>{{ $t('pages.techtree.current_level') }} <b>{{ page.level }}</b></span><span class="tech-availability" :class="page.available ? 'is-ready' : 'is-missing'">{{ $t(page.available ? 'pages.techtree.fulfilled' : 'pages.techtree.missing') }}</span></div>
			</div>
			<ModalLink navigate :href="'/info/' + page.id" class="button" aria-haspopup="dialog">{{ $t('pages.techtree.description') }}</ModalLink>
		</header>
		<section class="tech-dependencies">
			<header class="tech-dependencies-heading"><h2>{{ $t('pages.techtree.requirement_tree') }}</h2><span>{{ $t('pages.techtree.level_hint') }}</span></header>
			<ul v-if="requirements.length" class="tech-dependency-list">
				<DependencyNode v-for="requirement in requirements" :key="page.id + ':' + requirement.id" :requirement="requirement" :items="page.items"/>
			</ul>
			<div v-else class="tech-detail-empty" :title="$t('pages.techtree.no_requirements')">—</div>
		</section>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { Head, Link } from '@inertiajs/vue3';
	import { ModalLink } from '@inertiaui/modal-vue';
	import DependencyNode from '~/components/Page/Tech/DependencyNode.vue';

	defineOptions({ layout: { view: { resources: false } } });
	const props = defineProps({ page: Object });
	const requirements = computed(() => props.page.items.find((item) => item.id === props.page.id)?.requirments || []);
</script>