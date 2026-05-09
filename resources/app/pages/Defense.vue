<template>
	<Head title="Оборона"/>
	<div class="page-building page-building-unit">
		<div class="buldings">
			<div ref="activeRef" class="buldings-header" :style="{ backgroundImage: 'url(\'/assets/images/defense-bg.webp\')' }">
				<div class="buldings-header-main">
					<span class="title">
						Оборона / {{ planet['name'] }}
					</span>
					<Link href="/fleet" class="button">
						Флот
					</Link>
				</div>
				<UnitActive v-if="activeItem" :item="activeItem" @close="selectAction(null)" @build="buildAction"/>
			</div>
			<div class="buldings-list">
				<UnitItem v-for="(item, i) in items"
					:class="{ active: activeElement === item['id'] }"
					:key="i"
					:item="item"
					@select="selectAction(item['id'])"
				/>
			</div>
		</div>

		<UnitQueue :queue="queueByType('unit')"/>
	</div>
</template>

<script setup>
	import UnitQueue from '~/components/Page/Buildings/UnitQueue.vue';
	import { computed, ref } from 'vue';
	import UnitItem from '~/components/Page/Buildings/UnitItem.vue';
	import UnitActive from '~/components/Page/Buildings/UnitActive.vue';
	import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
	import { queueByType } from '~/utils/buildings.js';

	const props = defineProps({
		items: {
			type: Array,
			default: () => []
		}
	});

	const page = usePage();
	const planet = computed(() => page.props.planet);

	const activeElement = ref(null);
	const activeItem = computed(() => {
		return props.items.filter((item) => item.id === activeElement.value)[0] || null;
	});

	function selectAction(id) {
		if (activeElement.value !== id) {
			activeElement.value = id;
		} else {
			activeElement.value = null;
		}
	}

	function buildAction (id, count) {
		let data = {
			element: {}
		};
		data.element[id] = count;

		useForm(data)
			.post('/defense/queue', {
				preserveUrl: true,
				preserveScroll: true,
			});
	}
</script>