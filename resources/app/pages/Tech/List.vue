<template>
	<Head :title="$t('pages.techtree.title')"/>
	<div class="page-techtree">
		<header class="tech-heading">
			<h1>{{ $t('pages.techtree.title') }}</h1>
		</header>
		<UiTabs :items="tabs" :label="$t('pages.techtree.title')">
			<template #panel="{ item: group }">
				<div class="tech-list-heading"><span>{{ group.title }}</span><span>{{ $t('pages.techtree.requirements') }}</span></div>
				<div v-for="item in group.items" :key="item.id" class="tech-list-row">
					<div class="tech-list-identity">
						<ModalLink navigate :href="'/info/' + item.id" class="tech-list-name" aria-haspopup="dialog">
							<img :src="'/assets/images/elements/' + item.id + '.webp'" alt="" width="40" height="40" loading="lazy">
							<span>{{ item.name }}</span>
						</ModalLink>
					</div>
					<div class="tech-list-requirements">
						<div v-if="item.required !== null" class="tech-required-text" v-html="item.required"></div>
						<span v-else class="tech-no-requirements" :title="$t('pages.techtree.no_requirements')">—</span>
						<Link v-if="item.required !== null" :href="'/tech/' + item.id" class="tech-tree-link" :title="$t('pages.techtree.tree_for', { name: item.name })" :aria-label="$t('pages.techtree.tree_for', { name: item.name })">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v4M5 16v-4h14v4"/><rect x="9" y="2" width="6" height="6" rx="1"/><rect x="2" y="16" width="6" height="6" rx="1"/><rect x="16" y="16" width="6" height="6" rx="1"/></svg>
						</Link>
					</div>
				</div>
			</template>
		</UiTabs>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { UiTabs } from '~/components/UI';
	import { Head, Link } from '@inertiajs/vue3';
	import { ModalLink } from '@inertiaui/modal-vue';

	defineOptions({ layout: { view: { resources: false } } });
	const props = defineProps({ page: Object });
	const tabs = computed(() => props.page.items.map((group, id) => ({ ...group, id, label: group.title })));
</script>