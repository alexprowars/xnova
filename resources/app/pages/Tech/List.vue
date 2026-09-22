<template>
	<Head :title="$t('pages.techtree.title')"/>
	<div class="page-techtree">
		<header class="tech-heading">
			<h1>{{ $t('pages.techtree.title') }}</h1>
		</header>
		<UiTabs :items="tabs" :label="$t('pages.techtree.title')">
			<template #panel="{ item: group }">
				<div class="tech-list-heading">
					<span>{{ group.title }}</span>
					<span>{{ $t('pages.techtree.requirements') }}</span>
				</div>
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
						<Link
							v-if="item.required !== null"
							:href="'/tech/' + item.id"
							class="tech-tree-link button is-secondary icon-button"
							:title="$t('pages.techtree.tree_for', { name: item.name })"
							:aria-label="$t('pages.techtree.tree_for', { name: item.name })"
						>
							<TechTreeIcon stroke-width="1.5" aria-hidden="true"/>
						</Link>
					</div>
				</div>
			</template>
		</UiTabs>
	</div>
</template>

<script setup>
	import TechTreeIcon from '~/images/icons/tech-tree.svg?component';
	import { computed } from 'vue';
	import { UiTabs } from '~/components/UI';
	import { Head, Link } from '@inertiajs/vue3';
	import { ModalLink } from '@inertiaui/modal-vue';

	defineOptions({ layout: { view: { resources: false } } });
	const props = defineProps({
		page: Object,
	});
	const tabs = computed(() => props.page.items.map((group, id) => ({ ...group, id, label: group.title })));
</script>