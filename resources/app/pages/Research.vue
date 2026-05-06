<template>
	<Head title="Исследования"/>
	<div class="page-building page-building-tech">
		<div class="block">
			<div class="content page-building-items">
				<div class="buldings">
					<div ref="activeRef" class="buldings-header" :style="{ backgroundImage: 'url(\'/assets/images/research-bg.webp\')' }">
						<div class="buldings-header-main">
							<span class="title">
								Исследования / {{ planet['name'] }}
							</span>
							<Link href="/tech" class="button">
								Технологии
							</Link>
						</div>
						<TechActive v-if="activeItem" :item="activeItem" @close="selectAction(null)" @build="buildAction(activeItem['id'])"/>
					</div>
					<div class="buldings-list">
						<TechItem v-for="(item, i) in items" :key="i" :item="item"
							:class="{ active: activeElement === item['id'] }"
							@select="selectAction(item['id'])"
							@build="buildAction(item['id'])"
						/>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { ref, watch, useTemplateRef, computed } from 'vue';
	import TechActive from '../components/Page/Buildings/TechActive.vue';
	import TechItem from '../components/Page/Buildings/TechItem.vue';
	import { Head, Link, router, usePage } from '@inertiajs/vue3';
	import { useAnimateScroll } from '../composables/useAnimateScroll.js';
	import { useApiPost } from '../composables/useApi.js';
	import { useErrorNotification } from '../composables/useToast.js';

	const props = defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	})

	const activeRef = useTemplateRef('activeRef');

	const page = usePage();
	const planet = computed(() => page.props.planet);

	const activeElement = ref(null);
	const activeItem = computed(() => {
		return props.items.filter((item) => item.id === activeElement.value)[0] || null;
	});

	watch(activeElement, (value) => {
		if (!value) {
			return;
		}

		useAnimateScroll(activeRef.value, 500, { padding: -50 });
	});

	function selectAction(id) {
		if (activeElement.value !== id) {
			activeElement.value = id;
		} else {
			activeElement.value = null;
		}
	}

	async function buildAction (id) {
		try {
			await useApiPost('/research/search', { element: id });

			router.reload();
		} catch (e) {
			useErrorNotification(e.message);
		}
	}
</script>