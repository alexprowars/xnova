<template>
	<nav class="ui-tab-list ui-tab-navigation" :class="{ 'ui-tab-navigation--embedded': embedded }" :aria-label="label">
		<Link v-for="item in items" :key="item.id" :href="item.href" class="ui-tab-trigger" :aria-current="item.id === active ? 'page' : undefined">
			{{ item.label }}<UiCount v-if="item.count !== undefined && item.count !== null">{{ item.count }}</UiCount>
		</Link>
	</nav>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
	import UiCount from './UiCount.vue';

	defineProps({
		items: {
			type: Array,
			required: true,
		},
		label: {
			type: String,
			required: true,
		},
		active: [String, Number],
		embedded: Boolean,
	});
</script>
